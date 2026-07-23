<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Paiement;
use App\Models\Vente;
use App\Models\VenteDetail;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class CashierPosController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show pending orders dashboard for cashier.
     */
    public function index()
    {
        $pendingOrders = $this->orderService->getPendingPaymentOrders();
        $readyOrders = $this->orderService->getReadyForPaymentOrders();
        
        // Get today's stats
        $todayPaid = Commande::kitchen()
            ->payee()
            ->whereDate('updated_at', today())
            ->count();
        
        $todayRevenue = Commande::kitchen()
            ->payee()
            ->whereDate('updated_at', today())
            ->sum('total');

        return view('cashier.pending-orders', compact(
            'pendingOrders',
            'readyOrders',
            'todayPaid',
            'todayRevenue'
        ));
    }

    /**
     * Show order details for payment.
     */
    public function show(Commande $commande)
    {
        if (!$commande->isKitchenOrder()) {
            abort(404, 'Commande non trouvée');
        }

        if ($commande->isPaid()) {
            return redirect()
                ->route('cashier.pending')
                ->with('info', 'Cette commande est déjà payée.');
        }

        $paymentOrders = $this->resolvePaymentOrders($commande);

        if ($paymentOrders->isEmpty()) {
            return redirect()
                ->route('cashier.pending')
                ->with('info', 'Aucune commande prête à encaisser pour cette table.');
        }

        $paymentOrders = $this->reconcileOrderTotals($paymentOrders);

        $combinedTotal = (float) $paymentOrders->sum(fn (Commande $order) => (float) $order->total);

        return view('cashier.payment', compact('commande', 'paymentOrders', 'combinedTotal'));
    }
    
    /**
     * Alias for show() method to match test route name expectations.
     */
    public function showOrder(Commande $commande)
    {
        return $this->show($commande);
    }

    /**
     * Process payment for an order.
     */
    public function processPayment(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'payment_method'   => 'required|in:cash,carte,mixte',
            'amount_received'  => 'nullable|numeric|min:0',
            'card_amount'      => 'nullable|numeric|min:0',
            'cash_amount'      => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        if (!$commande->isKitchenOrder()) {
            return back()->with('error', 'Commande invalide.');
        }

        if ($commande->isPaid()) {
            return back()->with('error', 'Cette commande est déjà payée.');
        }

        try {
            $paymentOrders = $this->resolvePaymentOrders($commande);

            if ($paymentOrders->isEmpty()) {
                throw new \InvalidArgumentException('Aucune commande prête à encaisser pour cette table.');
            }

            $paymentOrders = $this->reconcileOrderTotals($paymentOrders);

            $paymentMethod  = $validated['payment_method'];
            $amountReceived = $validated['amount_received'] ?? null;
            $rawTotal       = (float) $paymentOrders->sum(fn (Commande $order) => (float) $order->total);

            // Apply discount
            $discountPct    = (float) ($validated['discount_percent'] ?? 0);
            $discountAmt    = round($rawTotal * $discountPct / 100, 2);
            $totalDue       = round($rawTotal - $discountAmt, 2);
            $validated['discount_percent'] = $discountPct;
            $validated['discount_amount']  = $discountAmt;
            $validated['total_due']        = $totalDue;

            // Handle mixed payment validation
            if ($paymentMethod === 'mixte') {
                $cashAmount = $validated['cash_amount'] ?? 0;
                $cardAmount = $validated['card_amount'] ?? 0;

                if (($cashAmount + $cardAmount) < $totalDue) {
                    throw new \InvalidArgumentException('Le montant total est insuffisant.');
                }
            }

            $paymentAllocations = $this->buildPaymentAllocations($paymentOrders, $validated);

            foreach ($paymentOrders as $paymentOrder) {
                $this->orderService->processKitchenOrderPayment(
                    $paymentOrder,
                    $paymentMethod,
                    $paymentMethod === 'cash' ? $amountReceived : null
                );

                $this->createPaymentRecord(
                    $paymentOrder,
                    array_merge($validated, $paymentAllocations[$paymentOrder->id] ?? [])
                );
            }

            $primaryOrder = $paymentOrders->first();
            $orderIds = $paymentOrders->pluck('id')->implode(',');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement effectué avec succès',
                    'commande' => $primaryOrder?->fresh(),
                    'print_url' => route('cashier.receipt.print', [
                        'commandeId'       => $primaryOrder?->getKey(),
                        'order_ids'        => $orderIds,
                        'payment_method'   => $paymentMethod,
                        'change'           => $this->resolveChangeAmount($totalDue, $validated),
                        'discount_percent' => $discountPct,
                        'discount_amount'  => $discountAmt,
                    ]),
                    'redirect_url' => route('cashier.pending'),
                ]);
            }

            return redirect()
                ->route('cashier.receipt.print', [
                    'commandeId'       => $primaryOrder?->getKey(),
                    'order_ids'        => $orderIds,
                    'payment_method'   => $paymentMethod,
                    'change'           => $this->resolveChangeAmount($totalDue, $validated),
                    'discount_percent' => $discountPct,
                    'discount_amount'  => $discountAmt,
                ])
                ->with('success', 'Paiement de ' . number_format($totalDue, 2) . ' DH effectué pour la table ' . ($commande->table?->numero ?? 'N/A'));
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    protected function resolveChangeAmount(float $totalDue, array $paymentData): float
    {
        if (($paymentData['payment_method'] ?? null) !== 'cash') {
            return 0;
        }

        $amountReceived = (float) ($paymentData['amount_received'] ?? 0);
        // Use pre-computed discounted total when available
        $due = (float) ($paymentData['total_due'] ?? $totalDue);

        return max(0, round($amountReceived - $due, 2));
    }

    protected function createPaymentRecord(Commande $commande, array $paymentData): void
    {
        $paymentMethod = $paymentData['payment_method'];
        $paymentAmount = (float) ($paymentData['payment_amount'] ?? $commande->total);

        // Build discount note when a discount was applied
        $discountPct = (float) ($paymentData['discount_percent'] ?? 0);
        $discountAmt = (float) ($paymentData['discount_amount']  ?? 0);
        $discountNote = $discountPct > 0
            ? sprintf('Remise %.1f%% (-%.2f DH)', $discountPct, $discountAmt)
            : null;

        // 1. Insert the sale into the ventes table
        $vente = Vente::create([
            'user_id'        => Auth::id() ?? $commande->user_id,
            'table_id'       => $commande->table_id,
            'total'          => $paymentAmount,
            'payment_method' => $paymentMethod === 'mixte' ? 'mixte' : ($paymentMethod === 'carte' ? 'carte' : 'cash'),
            'status'         => 'paid',
        ]);

        // 2. Insert details into the vente_details table
        $commande->loadMissing('details.produit');
        foreach ($commande->details as $detail) {
            VenteDetail::create([
                'vente_id'   => $vente->id,
                'produit_id' => $detail->produit_id,
                'quantity'   => $detail->quantity,
                'price'      => $detail->price,
                'total_line' => $detail->quantity * $detail->price,
            ]);
        }

        // 3. Insert the payment record with both commande_id and vente_id
        if ($paymentMethod === 'mixte') {
            // Create two payment records for mixed payments
            if (($paymentData['cash_amount'] ?? 0) > 0) {
                Paiement::create([
                    'commande_id' => $commande->id,
                    'vente_id'    => $vente->id,
                    'amount'      => $paymentData['cash_amount'],
                    'method'      => 'cash',
                    'reference'   => 'PAY-' . strtoupper(uniqid()),
                    'user_id'     => Auth::id(),
                    'notes'       => $discountNote,
                ]);
            }

            if (($paymentData['card_amount'] ?? 0) > 0) {
                Paiement::create([
                    'commande_id' => $commande->id,
                    'vente_id'    => $vente->id,
                    'amount'      => $paymentData['card_amount'],
                    'method'      => 'carte',
                    'reference'   => 'PAY-' . strtoupper(uniqid()),
                    'user_id'     => Auth::id(),
                    'notes'       => $discountNote,
                ]);
            }
        } else {
            Paiement::create([
                'commande_id' => $commande->id,
                'vente_id'    => $vente->id,
                'amount'      => $paymentAmount,
                'method'      => $paymentMethod === 'carte' ? 'carte' : 'cash',
                'reference'   => 'PAY-' . strtoupper(uniqid()),
                'user_id'     => Auth::id(),
                'notes'       => $discountNote,
            ]);
        }
    }

    /**
     * Get pending orders (AJAX).
     */
    public function getPendingOrders()
    {
        $orders = $this->orderService->getPendingPaymentOrders();
        
        return response()->json([
            'orders' => $orders,
            'count' => $orders->count(),
        ]);
    }

    /**
     * Print receipt for paid order.
     */
    public function printReceipt(Commande $commande)
    {
        if (!$commande->isPaid()) {
            return back()->with('error', 'Cette commande n\'est pas encore payée.');
        }

        $commande->load(['details.produit', 'table', 'user']);
        
        $pdf = Pdf::loadView('cashier.receipt', compact('commande'));
        
        return $pdf->download("recu-{$commande->id}.pdf");
    }

    /**
     * Display a printer-friendly receipt page and auto-print it in the browser.
     */
    public function showPrintableReceipt(Request $request, int $commandeId)
    {
        $commande = Commande::findOrFail($commandeId);
        $orderIds = collect(explode(',', (string) $request->input('order_ids')))
            ->map(fn (string $value) => (int) trim($value))
            ->filter()
            ->unique()
            ->values();

        if ($orderIds->isEmpty()) {
            $orderIds = collect([$commande->id]);
        }

        $orders = Commande::whereIn('id', $orderIds)
            ->with(['details.produit', 'table', 'user'])
            ->get();

        if ($orders->isEmpty() || $orders->contains(fn (Commande $order) => !$order->isPaid())) {
            return redirect()->route('cashier.pending')
                ->with('error', 'Cette commande n\'est pas encore payée.');
        }

        $orders = $this->reconcileOrderTotals($orders);

        $paymentMethod = $request->string('payment_method')->value() ?: 'cash';
        $changeAmount  = max(0, (float) $request->input('change', 0));
        $totalAmount   = (float) $orders->sum(fn (Commande $order) => (float) $order->total);
        $discountPct   = (float) $request->input('discount_percent', 0);
        $discountAmt   = (float) $request->input('discount_amount', 0);
        $netAmount     = $discountAmt > 0 ? round($totalAmount - $discountAmt, 2) : $totalAmount;

        return view('cashier.receipt-print', [
            'orders'          => $orders,
            'commande'        => $commande,
            'totalAmount'     => $totalAmount,
            'netAmount'       => $netAmount,
            'discountPercent' => $discountPct,
            'discountAmount'  => $discountAmt,
            'paymentMethod'   => $paymentMethod,
            'changeAmount'    => $changeAmount,
            'redirectUrl'     => route('cashier.pending'),
        ]);
    }

    /**
     * Show paid orders history (includes cancelled orders for audit visibility).
     */
    public function history(Request $request)
    {
        $query = Commande::kitchen()->whereIn('status', ['payee', 'annule']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('updated_at', $request->date);
        } else {
            $query->whereDate('updated_at', today());
        }

        $orders = $query->with(['details.produit', 'table', 'user'])
            ->latest('updated_at')
            ->paginate(20);

        // Revenue only from paid orders (exclude cancelled)
        $revenueQuery = Commande::kitchen()->payee();
        if ($request->filled('date')) {
            $revenueQuery->whereDate('updated_at', $request->date);
        } else {
            $revenueQuery->whereDate('updated_at', today());
        }
        $totalRevenue = $revenueQuery->sum('total');

        // Count cancelled orders for the alert
        $cancelledQuery = Commande::kitchen()->where('status', 'annule');
        if ($request->filled('date')) {
            $cancelledQuery->whereDate('updated_at', $request->date);
        } else {
            $cancelledQuery->whereDate('updated_at', today());
        }
        $cancelledCount = $cancelledQuery->count();
        $cancelledTotal = $cancelledQuery->sum('total');

        return view('cashier.history', compact('orders', 'totalRevenue', 'cancelledCount', 'cancelledTotal'));
    }

    /**
     * Cancel a paid kitchen sale from cashier history (admin only).
     */
    public function cancelHistorySale(Commande $commande)
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'Seul un administrateur peut annuler une vente.');
        }

        if (!$commande->isKitchenOrder()) {
            abort(404, 'Commande non trouvée');
        }

        if ($commande->status === 'annule') {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        if (!$commande->isPaid()) {
            return back()->with('error', 'Seules les ventes payées peuvent être annulées depuis cet écran.');
        }

        DB::transaction(function () use ($commande) {
            $commande->loadMissing(['details.produit', 'table']);
            $stockService = app(StockService::class);

            foreach ($commande->details as $detail) {
                if (!$detail->produit) {
                    continue;
                }

                $stockService->addStock(
                    $detail->produit,
                    $detail->quantity,
                    'ajustement',
                    $commande->id
                );
            }

            Paiement::where('commande_id', $commande->id)->update([
                'status' => 'refunded',
            ]);

            $commande->update(['status' => 'annule']);

            if ($commande->table) {
                $hasOtherOpenOrders = Commande::where('table_id', $commande->table_id)
                    ->where('id', '!=', $commande->id)
                    ->where('type', 'kitchen')
                    ->whereNotIn('status', ['payee', 'annule'])
                    ->exists();

                if (!$hasOtherOpenOrders) {
                    $commande->table()->update(['status' => 'free']);
                }
            }

            $commande->logCustomAction('cancel', "Vente cuisine #{$commande->id} annulée par l'administrateur");
        });

        return redirect()
            ->route('cashier.history', ['date' => request('date')])
            ->with('success', 'Vente annulée avec succès. Stock restauré.');
    }

    /**
     * Admin ticket center for cashier revenues.
     */
    public function tickets(Request $request)
    {
        $dateStart = $request->input('date_start', today()->toDateString());
        $dateEnd = $request->input('date_end', today()->toDateString());

        if ($dateStart > $dateEnd) {
            [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
        }

        $query = $this->paidOrdersByDateRange($dateStart, $dateEnd);
        $totalRevenue = (float) (clone $query)->sum('total');
        $salesCount = (int) (clone $query)->count();

        return view('cashier.tickets', compact('dateStart', 'dateEnd', 'totalRevenue', 'salesCount'));
    }

    /**
     * Print a daily summary or detailed ticket.
     */
    public function printTicket(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:summary,detailed',
        ]);

        $ticketDate = Carbon::parse($validated['date'])->toDateString();
        $ticketType = $validated['type'];

        $query = $this->paidOrdersByDateRange($ticketDate, $ticketDate)
            ->with(['details.produit', 'table', 'user'])
            ->orderBy('updated_at');

        $orders = $query->get();
        $totalRevenue = (float) $orders->sum('total');
        $productSales = $ticketType === 'detailed'
            ? $this->buildProductSalesSummary($orders)
            : collect();

        return view('cashier.ticket-print', [
            'orders' => $orders,
            'productSales' => $productSales,
            'ticketType' => $ticketType,
            'ticketDate' => $ticketDate,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    /**
     * Export revenue report to PDF (date range + detail mode).
     */
    public function exportTicketsPdf(Request $request)
    {
        $validated = $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'type' => 'required|in:summary,detailed',
        ]);

        $dateStart = Carbon::parse($validated['date_start'])->toDateString();
        $dateEnd = Carbon::parse($validated['date_end'])->toDateString();
        $reportType = $validated['type'];

        $baseQuery = $this->paidOrdersByDateRange($dateStart, $dateEnd);
        $totalRevenue = (float) (clone $baseQuery)->sum('total');
        $salesCount = (int) (clone $baseQuery)->count();

        $orders = $reportType === 'detailed'
            ? (clone $baseQuery)->with(['details.produit', 'table', 'user'])->orderBy('updated_at')->get()
            : collect();
        $productSales = $reportType === 'detailed'
            ? $this->buildProductSalesSummary($orders)
            : collect();

        $pdf = Pdf::loadView('cashier.ticket-report-pdf', [
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'reportType' => $reportType,
            'salesCount' => $salesCount,
            'totalRevenue' => $totalRevenue,
            'orders' => $orders,
            'productSales' => $productSales,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("rapport-caissier-{$dateStart}-{$dateEnd}.pdf");
    }

    /**
     * Get cashier dashboard stats (AJAX).
     */
    public function stats()
    {
        $pendingCount = $this->orderService->getPendingPaymentOrders()->count();
        $readyCount = Commande::kitchen()->readyForPayment()->count();
        $todayPaid = Commande::kitchen()->payee()->whereDate('updated_at', today())->count();
        $todayRevenue = Commande::kitchen()->payee()->whereDate('updated_at', today())->sum('total');

        return response()->json([
            'pending_count' => $pendingCount,
            'ready_count' => $readyCount,
            'today_paid' => $todayPaid,
            'today_revenue' => number_format($todayRevenue, 2),
        ]);
    }

    protected function resolvePaymentOrders(Commande $commande)
    {
        if (!$commande->table_id) {
            $commande->load(['details.produit', 'table', 'user']);

            return collect([$commande]);
        }

        return $this->orderService->getReadyPaymentOrdersForTable((int) $commande->table_id);
    }

    protected function reconcileOrderTotals(Collection $orders): Collection
    {
        return $orders->map(function (Commande $order) {
            $order->loadMissing('details');

            $detailsTotal = (float) $order->details->sum(
                fn (CommandeDetail $detail) => ((float) $detail->price) * ((int) $detail->quantity)
            );

            if (abs(((float) $order->total) - $detailsTotal) > 0.009) {
                $order->forceFill(['total' => $detailsTotal])->saveQuietly();
                $order->setAttribute('total', $detailsTotal);
            }

            return $order;
        });
    }

    protected function buildPaymentAllocations($paymentOrders, array $paymentData): array
    {
        $allocations   = [];
        $paymentMethod = $paymentData['payment_method'];
        $discountPct   = (float) ($paymentData['discount_percent'] ?? 0);

        // Helper: compute the discounted net total for a single order line
        $netForOrder = function (Commande $order) use ($discountPct): float {
            $raw        = (float) $order->total;
            $discountAmt = round($raw * $discountPct / 100, 2);
            return round($raw - $discountAmt, 2);
        };

        if ($paymentMethod !== 'mixte') {
            foreach ($paymentOrders as $paymentOrder) {
                $net         = $netForOrder($paymentOrder);
                $discountAmt = round((float) $paymentOrder->total - $net, 2);
                $allocations[$paymentOrder->id] = [
                    'payment_amount'   => $net,
                    'discount_percent' => $discountPct,
                    'discount_amount'  => $discountAmt,
                ];
            }

            return $allocations;
        }

        // Mixte: split the submitted cash/card amounts proportionally across orders,
        // but cap each order at its discounted net total.
        $remainingCash = (float) ($paymentData['cash_amount'] ?? 0);
        $remainingCard = (float) ($paymentData['card_amount'] ?? 0);

        foreach ($paymentOrders as $paymentOrder) {
            $net         = $netForOrder($paymentOrder);
            $discountAmt = round((float) $paymentOrder->total - $net, 2);

            $cashPortion = round(min($remainingCash, $net), 2);
            $remainingCash -= $cashPortion;

            $cardPortion = round($net - $cashPortion, 2);
            $remainingCard -= $cardPortion;

            $allocations[$paymentOrder->id] = [
                'payment_amount'   => $net,
                'cash_amount'      => $cashPortion,
                'card_amount'      => $cardPortion,
                'discount_percent' => $discountPct,
                'discount_amount'  => $discountAmt,
            ];
        }

        return $allocations;
    }

    protected function paidOrdersByDateRange(string $dateStart, string $dateEnd)
    {
        return Commande::kitchen()
            ->payee()
            ->whereDate('updated_at', '>=', $dateStart)
            ->whereDate('updated_at', '<=', $dateEnd);
    }

    protected function buildProductSalesSummary($orders)
    {
        $orderIds = $orders->pluck('id')->filter()->values();

        if ($orderIds->isEmpty()) {
            return collect();
        }

        return CommandeDetail::query()
            ->selectRaw('produit_id, SUM(quantity) as total_quantity, SUM(quantity * price) as total_amount')
            ->whereIn('commande_id', $orderIds)
            ->with('produit:id,name')
            ->groupBy('produit_id')
            ->orderByDesc('total_quantity')
            ->get();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'payment_method' => 'required|in:cash,carte,mixte',
            'amount_received' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
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

            $paymentMethod = $validated['payment_method'];
            $amountReceived = $validated['amount_received'] ?? null;
            $totalDue = (float) $paymentOrders->sum(fn (Commande $order) => (float) $order->total);

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
                        'commandeId' => $primaryOrder?->getKey(),
                        'order_ids' => $orderIds,
                        'payment_method' => $paymentMethod,
                        'change' => $this->resolveChangeAmount($totalDue, $validated),
                    ]),
                    'redirect_url' => route('cashier.pending'),
                ]);
            }

            return redirect()
                ->route('cashier.receipt.print', [
                    'commandeId' => $primaryOrder?->getKey(),
                    'order_ids' => $orderIds,
                    'payment_method' => $paymentMethod,
                    'change' => $this->resolveChangeAmount($totalDue, $validated),
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

        return max(0, round($amountReceived - $totalDue, 2));
    }

    /**
     * Create payment record in paiements table.
     */
    protected function createPaymentRecord(Commande $commande, array $paymentData): void
    {
        $paymentMethod = $paymentData['payment_method'];
        $paymentAmount = (float) ($paymentData['payment_amount'] ?? $commande->total);
        
        if ($paymentMethod === 'mixte') {
            // Create two payment records for mixed payments
            if (($paymentData['cash_amount'] ?? 0) > 0) {
                Paiement::create([
                    'commande_id' => $commande->id,
                    'amount' => $paymentData['cash_amount'],
                    'method' => 'cash',
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'user_id' => Auth::id(),
                ]);
            }
            
            if (($paymentData['card_amount'] ?? 0) > 0) {
                Paiement::create([
                    'commande_id' => $commande->id,
                    'amount' => $paymentData['card_amount'],
                    'method' => 'carte',
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'user_id' => Auth::id(),
                ]);
            }
        } else {
            Paiement::create([
                'commande_id' => $commande->id,
                'amount' => $paymentAmount,
                'method' => $paymentMethod,
                'reference' => 'PAY-' . strtoupper(uniqid()),
                'user_id' => Auth::id(),
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

        $paymentMethod = $request->string('payment_method')->value() ?: 'cash';
        $changeAmount = max(0, (float) $request->input('change', 0));
        $totalAmount = (float) $orders->sum(fn (Commande $order) => (float) $order->total);

        return view('cashier.receipt-print', [
            'orders' => $orders,
            'commande' => $commande,
            'totalAmount' => $totalAmount,
            'paymentMethod' => $paymentMethod,
            'changeAmount' => $changeAmount,
            'redirectUrl' => route('cashier.pending'),
        ]);
    }

    /**
     * Show paid orders history.
     */
    public function history(Request $request)
    {
        $query = Commande::kitchen()->payee();

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('updated_at', $request->date);
        } else {
            $query->whereDate('updated_at', today());
        }

        $orders = $query->with(['details.produit', 'table', 'user'])
            ->latest('updated_at')
            ->paginate(20);

        $totalRevenue = $query->sum('total');

        return view('cashier.history', compact('orders', 'totalRevenue'));
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

    protected function buildPaymentAllocations($paymentOrders, array $paymentData): array
    {
        $allocations = [];
        $paymentMethod = $paymentData['payment_method'];

        if ($paymentMethod !== 'mixte') {
            foreach ($paymentOrders as $paymentOrder) {
                $allocations[$paymentOrder->id] = [
                    'payment_amount' => (float) $paymentOrder->total,
                ];
            }

            return $allocations;
        }

        $remainingCash = (float) ($paymentData['cash_amount'] ?? 0);
        $remainingCard = (float) ($paymentData['card_amount'] ?? 0);

        foreach ($paymentOrders as $paymentOrder) {
            $orderTotal = (float) $paymentOrder->total;
            $cashPortion = min($remainingCash, $orderTotal);
            $remainingCash -= $cashPortion;

            $cardPortion = round($orderTotal - $cashPortion, 2);
            $remainingCard -= $cardPortion;

            $allocations[$paymentOrder->id] = [
                'payment_amount' => $orderTotal,
                'cash_amount' => $cashPortion,
                'card_amount' => $cardPortion,
            ];
        }

        return $allocations;
    }
}

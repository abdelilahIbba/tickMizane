<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $commande->load(['details.produit', 'table', 'user']);
        
        return view('cashier.payment', compact('commande'));
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
            $paymentMethod = $validated['payment_method'];
            $amountReceived = $validated['amount_received'] ?? null;

            // Handle mixed payment validation
            if ($paymentMethod === 'mixte') {
                $cashAmount = $validated['cash_amount'] ?? 0;
                $cardAmount = $validated['card_amount'] ?? 0;
                
                if (($cashAmount + $cardAmount) < $commande->total) {
                    throw new \InvalidArgumentException('Le montant total est insuffisant.');
                }
            }

            // Process the payment
            $this->orderService->processKitchenOrderPayment(
                $commande,
                $paymentMethod,
                $amountReceived
            );

            // Create payment record
            $this->createPaymentRecord($commande, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement effectué avec succès',
                    'commande' => $commande->fresh(),
                ]);
            }

            return redirect()
                ->route('cashier.pending')
                ->with('success', "Paiement de {$commande->total} DH effectué pour la table {$commande->table->numero}");
                
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

    /**
     * Create payment record in paiements table.
     */
    protected function createPaymentRecord(Commande $commande, array $paymentData): void
    {
        $paymentMethod = $paymentData['payment_method'];
        
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
                'amount' => $commande->total,
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
        $pendingCount = Commande::kitchen()->pendingPayment()->count();
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
}

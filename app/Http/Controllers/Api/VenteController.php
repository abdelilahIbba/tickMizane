<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vente;
use App\Models\Paiement;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Vente::with(['user', 'table', 'paiements']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where('id', $request->search);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }

    /**
     * Display the specified sale.
     */
    public function show(Vente $vente)
    {
        $vente->load(['user', 'table', 'details.produit', 'paiements']);

        $remaining = $this->paymentService->getRemainingAmount($vente);

        return response()->json([
            'success' => true,
            'data' => [
                'vente' => $vente,
                'remaining_amount' => $remaining,
            ],
        ]);
    }

    /**
     * Get payments for a sale.
     */
    public function payments(Vente $vente)
    {
        $payments = $vente->paiements()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'vente' => $vente,
                'payments' => $payments,
                'total_paid' => $payments->sum('amount'),
                'remaining' => $this->paymentService->getRemainingAmount($vente),
            ],
        ]);
    }

    /**
     * Add payment to a sale.
     */
    public function addPayment(Request $request, Vente $vente)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,mobile,other',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $payment = $this->paymentService->processPayment(
                $vente,
                $validated['amount'],
                $validated['payment_method'],
                $validated['reference'] ?? null
            );

            $vente->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès.',
                'data' => [
                    'payment' => $payment,
                    'vente' => $vente->load(['paiements']),
                    'remaining' => $this->paymentService->getRemainingAmount($vente),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Refund a payment.
     */
    public function refund(Request $request, Paiement $payment)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        try {
            $refundAmount = $validated['amount'] ?? $payment->amount;
            
            $this->paymentService->refund(
                $payment,
                $refundAmount,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Remboursement effectué avec succès.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get today's sales for current user.
     */
    public function mySales()
    {
        $sales = Vente::where('user_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->with(['table', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->where('status', '!=', 'cancelled')->sum('total'),
            'completed' => $sales->where('status', 'completed')->count(),
            'pending' => $sales->whereIn('status', ['pending', 'partial'])->count(),
            'cancelled' => $sales->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get receipt data for printing.
     */
    public function receipt(Vente $vente)
    {
        $vente->load(['user', 'table', 'details.produit', 'paiements']);

        $receipt = [
            'vente_id' => $vente->id,
            'date' => $vente->created_at->format('d/m/Y H:i'),
            'cashier' => $vente->user->name ?? 'N/A',
            'table' => $vente->table ? "Table {$vente->table->number}" : null,
            'items' => $vente->details->map(function ($detail) {
                return [
                    'name' => $detail->produit->name,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->unit_price,
                    'discount' => $detail->discount,
                    'total' => $detail->total,
                ];
            }),
            'subtotal' => $vente->subtotal,
            'discount' => $vente->discount,
            'total' => $vente->total,
            'payments' => $vente->paiements->map(function ($payment) {
                return [
                    'method' => $payment->payment_method,
                    'amount' => $payment->amount,
                    'date' => $payment->created_at->format('d/m/Y H:i'),
                ];
            }),
            'total_paid' => $vente->paiements->sum('amount'),
            'remaining' => max(0, $vente->total - $vente->paiements->sum('amount')),
            'status' => $vente->status,
        ];

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }

    /**
     * Search sales.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $query = $validated['query'];

        $sales = Vente::where(function ($q) use ($query) {
            $q->where('id', $query)
              ->orWhereHas('table', function ($tq) use ($query) {
                  $tq->where('number', $query);
              })
              ->orWhereHas('user', function ($uq) use ($query) {
                  $uq->where('name', 'like', "%{$query}%");
              });
        })
        ->with(['user', 'table', 'paiements'])
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }

    /**
     * Get sales statistics.
     */
    public function stats(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Vente::query();

        if (!empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        $sales = $query->get();

        $stats = $this->paymentService->getPaymentStats(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        $salesStats = [
            'total_count' => $sales->count(),
            'completed_count' => $sales->where('status', 'completed')->count(),
            'pending_count' => $sales->whereIn('status', ['pending', 'partial'])->count(),
            'cancelled_count' => $sales->where('status', 'cancelled')->count(),
            'total_revenue' => $sales->where('status', '!=', 'cancelled')->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'average_sale' => $sales->where('status', '!=', 'cancelled')->avg('total') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'sales_stats' => $salesStats,
                'payment_stats' => $stats,
            ],
        ]);
    }
}

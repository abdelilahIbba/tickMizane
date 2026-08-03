<?php

namespace App\Http\Controllers;

use App\Models\Vente;
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
        $query = Vente::with(['user', 'details.produit', 'paiements'])
            ->latest();

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $ventes = $query->paginate(20);

        // Stats
        $todaySales = Vente::today()->paid()->sum('total');
        $todayCount = Vente::today()->paid()->count();
        $cashTotal = Vente::today()->paid()->where('payment_method', 'cash')->sum('total');
        $cardTotal = Vente::today()->paid()->where('payment_method', 'carte')->sum('total');

        return view('ventes.index', compact(
            'ventes',
            'todaySales',
            'todayCount',
            'cashTotal',
            'cardTotal'
        ));
    }

    /**
     * Display the specified sale.
     */
    public function show(Vente $vente)
    {
        $vente->load(['user', 'details.produit', 'paiements', 'table']);

        $remainingAmount = $this->paymentService->getRemainingAmount($vente);

        return view('ventes.show', compact('vente', 'remainingAmount'));
    }

    /**
     * Cancel a sale (only unpaid sales can be cancelled).
     */
    public function cancel(Vente $vente)
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'Seul un administrateur peut annuler une vente.');
        }

        if ($vente->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        try {
            $this->paymentService->cancelVente($vente);

            return redirect()
                ->route('ventes.index')
                ->with('success', 'Vente annulée avec succès. Stock restauré.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    /**
     * Add a payment to an existing sale.
     */
    public function addPayment(Request $request, Vente $vente)
    {
        if ($vente->status === 'paid') {
            return back()->with('error', 'Cette vente est déjà payée.');
        }

        if ($vente->status === 'cancelled') {
            return back()->with('error', 'Impossible d\'ajouter un paiement à une vente annulée.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,carte',
        ]);

        $remainingAmount = $this->paymentService->getRemainingAmount($vente);

        if ($validated['amount'] > $remainingAmount) {
            return back()->with('error', 'Le montant dépasse le solde restant.');
        }

        $this->paymentService->processPayment($vente, $validated['amount'], $validated['method']);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Get sales report for a date range.
     */
    public function report(Request $request)
    {
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', today()->toDateString());

        $ventes = Vente::with(['user', 'details.produit'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->paid()
            ->get();

        $stats = [
            'total_sales' => $ventes->sum('total'),
            'sales_count' => $ventes->count(),
            'average_sale' => $ventes->avg('total') ?? 0,
            'by_payment_method' => $ventes->groupBy('payment_method')->map->sum('total'),
            'by_date' => $ventes->groupBy(fn($v) => $v->created_at->format('Y-m-d'))->map->sum('total'),
        ];

        return view('ventes.report', compact('ventes', 'stats', 'dateFrom', 'dateTo'));
    }
}


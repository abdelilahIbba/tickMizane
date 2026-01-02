<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Vente;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Paiement::with(['vente.user'])
            ->latest();

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        $payments = $query->paginate(20);

        // Stats
        $todayTotal = Paiement::today()->sum('amount');
        $cashTotal = Paiement::today()->byMethod('cash')->sum('amount');
        $cardTotal = Paiement::today()->byMethod('carte')->sum('amount');
        $mixedTotal = Paiement::today()->byMethod('mixte')->sum('amount');

        return view('payments.index', compact(
            'payments',
            'todayTotal',
            'cashTotal',
            'cardTotal',
            'mixedTotal'
        ));
    }

    /**
     * Show the daily report.
     */
    public function report(Request $request)
    {
        $date = $request->date ?? today();

        $payments = Paiement::whereDate('created_at', $date)
            ->with('vente')
            ->get();

        $totalIn = $payments->sum('amount');
        $cashIn = $payments->where('method', 'cash')->sum('amount');
        $cardIn = $payments->where('method', 'carte')->sum('amount');

        return view('payments.report', compact(
            'payments',
            'totalIn',
            'cashIn',
            'cardIn',
            'date'
        ));
    }

    /**
     * Show a receipt for a payment.
     */
    public function receipt(Paiement $payment)
    {
        $payment->load(['vente.details.produit', 'vente.user']);

        return view('payments.receipt', [
            'paiement' => $payment,
            'vente' => $payment->vente,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Commande::with(['fournisseur', 'user', 'details.produit']);

        // Filter by fournisseur
        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commandes = $query->latest()->paginate(15);
        $fournisseurs = Fournisseur::orderBy('name')->get();

        // Statistics
        $stats = $this->orderService->getOrderStats();

        return view('orders.index', compact('commandes', 'fournisseurs', 'stats'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $fournisseurs = Fournisseur::orderBy('name')->get();
        $produits = Produit::active()->orderBy('name')->get();
        $suggestedReorders = $this->orderService->getSuggestedReorders();

        return view('orders.create', compact('fournisseurs', 'produits', 'suggestedReorders'));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        $fournisseur = Fournisseur::findOrFail($validated['fournisseur_id']);

        try {
            $commande = $this->orderService->createOrder($fournisseur, $validated['items']);

            return redirect()
                ->route('orders.show', $commande)
                ->with('success', 'Commande créée avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Commande $order)
    {
        $order->load(['fournisseur', 'user', 'details.produit']);

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the order.
     */
    public function edit(Commande $order)
    {
        if ($order->isReceived()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Impossible de modifier une commande déjà reçue.');
        }

        $order->load(['details.produit']);
        $fournisseurs = Fournisseur::orderBy('name')->get();
        $produits = Produit::active()->orderBy('name')->get();

        return view('orders.edit', compact('order', 'fournisseurs', 'produits'));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Commande $order)
    {
        if ($order->isReceived()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Impossible de modifier une commande déjà reçue.');
        }

        $validated = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        try {
            // Update fournisseur if changed
            if ($order->fournisseur_id != $validated['fournisseur_id']) {
                $order->update(['fournisseur_id' => $validated['fournisseur_id']]);
            }

            $this->orderService->updateOrder($order, $validated['items']);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Commande mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Mark an order as received.
     */
    public function receive(Commande $order)
    {
        try {
            $this->orderService->markReceived($order);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Commande marquée comme reçue. Stock mis à jour.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Commande $order)
    {
        if ($order->isReceived()) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Impossible de supprimer une commande déjà reçue.');
        }

        try {
            $this->orderService->cancelOrder($order);

            return redirect()
                ->route('orders.index')
                ->with('success', 'Commande supprimée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get suggested reorders (AJAX).
     */
    public function suggestions()
    {
        $suggestions = $this->orderService->getSuggestedReorders();

        return response()->json($suggestions);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Produit;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['produit']);

        // Filter by product
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by reason
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(20);
        $produits = Produit::orderBy('name')->get();

        // Stats
        $stats = [
            'total_in' => StockMovement::where('type', 'in')->sum('quantity'),
            'total_out' => StockMovement::where('type', 'out')->sum('quantity'),
        ];

        return view('stock.index', compact('movements', 'produits', 'stats'));
    }

    /**
     * Show the form for creating a new stock movement.
     */
    public function create()
    {
        $produits = Produit::orderBy('name')->get();
        return view('stock.create', compact('produits'));
    }

    /**
     * Store a newly created stock movement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:vente,commande,perte,ajustement',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $quantity = $validated['quantity'];

        // Check stock for 'out' movements
        $isAddition = $validated['type'] === 'in';
        
        if (!$isAddition && $produit->stock_quantity < $quantity) {
            return back()
                ->withInput()
                ->with('error', 'Stock insuffisant pour cette opération.');
        }

        // Create the movement
        StockMovement::create([
            'produit_id' => $validated['produit_id'],
            'type' => $validated['type'],
            'quantity' => $quantity,
            'reason' => $validated['reason'],
        ]);

        // Update product stock
        if ($isAddition) {
            $produit->increment('stock_quantity', $quantity);
        } else {
            $produit->decrement('stock_quantity', $quantity);
        }

        return redirect()
            ->route('stock.index')
            ->with('success', 'Mouvement de stock enregistré avec succès.');
    }

    /**
     * Display the specified stock movement.
     */
    public function show(StockMovement $stock)
    {
        $stock->load(['produit.category']);

        return view('stock.show', compact('stock'));
    }

    /**
     * Show the form for editing the stock movement.
     */
    public function edit(StockMovement $stock)
    {
        $produits = Produit::orderBy('name')->get();

        return view('stock.edit', compact('stock', 'produits'));
    }

    /**
     * Update the specified stock movement.
     */
    public function update(Request $request, StockMovement $stock)
    {
        $validated = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:vente,commande,perte,ajustement',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $oldStock = $stock->produit;
        $oldQuantity = $stock->quantity;
        $oldType = $stock->type;

        // Reverse the old movement
        if ($oldType === 'in') {
            $oldStock->decrement('stock_quantity', $oldQuantity);
        } else {
            $oldStock->increment('stock_quantity', $oldQuantity);
        }

        // Check new stock availability for 'out' movements
        if ($validated['type'] === 'out' && $produit->stock_quantity < $validated['quantity']) {
            // Restore old movement
            if ($oldType === 'in') {
                $oldStock->increment('stock_quantity', $oldQuantity);
            } else {
                $oldStock->decrement('stock_quantity', $oldQuantity);
            }

            return back()
                ->withInput()
                ->with('error', 'Stock insuffisant pour cette opération.');
        }

        // Update the movement
        $stock->update($validated);

        // Apply new movement
        if ($validated['type'] === 'in') {
            $produit->increment('stock_quantity', $validated['quantity']);
        } else {
            $produit->decrement('stock_quantity', $validated['quantity']);
        }

        return redirect()
            ->route('stock.index')
            ->with('success', 'Mouvement de stock mis à jour avec succès.');
    }

    /**
     * Remove the specified stock movement.
     */
    public function destroy(StockMovement $stock)
    {
        $produit = $stock->produit;
        $quantity = $stock->quantity;
        $type = $stock->type;

        // Reverse the movement effect on stock
        if ($type === 'in') {
            if ($produit->stock_quantity < $quantity) {
                return redirect()
                    ->route('stock.index')
                    ->with('error', 'Impossible de supprimer ce mouvement car le stock deviendrait négatif.');
            }
            $produit->decrement('stock_quantity', $quantity);
        } else {
            $produit->increment('stock_quantity', $quantity);
        }

        $stock->delete();

        return redirect()
            ->route('stock.index')
            ->with('success', 'Mouvement de stock supprimé avec succès.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\StockMovement;
use App\Models\Paiement;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index(Request $request)
    {
        $categories = Category::active()->get();
        $products = Produit::active()
            ->with('category')
            ->get();
        
        $table = null;
        if ($request->has('table')) {
            $table = Table::find($request->table);
        }
        
        return view('pos.index', compact('categories', 'products', 'table'));
    }

    /**
     * Process checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,carte,mixte',
            'table_id' => 'nullable|exists:tables,id',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total
            $total = 0;
            $items = [];
            
            foreach ($request->items as $item) {
                $produit = Produit::find($item['id']);
                
                // Check stock
                if ($produit->stock_quantity < $item['quantity']) {
                    throw new \Exception("Stock insuffisant pour {$produit->name}");
                }
                
                $lineTotal = $produit->price_vente * $item['quantity'];
                $total += $lineTotal;
                
                $items[] = [
                    'produit' => $produit,
                    'quantity' => $item['quantity'],
                    'price' => $produit->price_vente,
                    'total_line' => $lineTotal,
                ];
            }

            // Create vente
            $vente = Vente::create([
                'user_id' => Auth::id(),
                'table_id' => $request->table_id,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'status' => 'paid',
            ]);

            // Create vente details and update stock
            foreach ($items as $item) {
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $item['produit']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_line' => $item['total_line'],
                ]);

                // Decrement stock
                $item['produit']->decrementStock($item['quantity']);

                // Record stock movement
                StockMovement::create([
                    'produit_id' => $item['produit']->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reason' => 'vente',
                    'reference_id' => $vente->id,
                ]);
            }

            // Create paiement
            Paiement::create([
                'vente_id' => $vente->id,
                'amount' => $total,
                'method' => $request->payment_method,
            ]);

            // Free table if assigned
            if ($request->table_id) {
                Table::find($request->table_id)?->markFree();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vente enregistrée avec succès',
                'vente_id' => $vente->id,
                'total' => $total,
                'change' => $request->amount_received ? $request->amount_received - $total : 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

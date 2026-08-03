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
use App\Support\SuperAdmin;
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
        $existingVente = null;
        
        if ($request->has('table')) {
            $table = Table::with('currentVente.details.produit')->find($request->table);
            
            // If table has an unpaid vente, load it to allow adding more items
            if ($table && $table->currentVente && $table->currentVente->status === 'unpaid') {
                $existingVente = $table->currentVente;
            }
        }
        
        return view('pos.index', compact('categories', 'products', 'table', 'existingVente'));
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

            $isTableOrder = !empty($request->table_id);

            // Check if there's an existing unpaid vente for this table
            $existingVente = null;
            if ($isTableOrder) {
                $table = Table::with('currentVente')->find($request->table_id);
                if ($table && $table->currentVente && $table->currentVente->status === 'unpaid') {
                    $existingVente = $table->currentVente;
                }
            }

            // Either update existing vente or create new one
            if ($existingVente) {
                // ADD TO EXISTING VENTE
                $vente = $existingVente;
                
                // Add new items to the vente
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
                
                // Update total
                $vente->update([
                    'total' => $vente->total + $total,
                    'payment_method' => $request->payment_method,
                ]);
                
                $message = 'Articles ajoutés à la commande.';
            } else {
                // CREATE NEW VENTE
                // For table orders: status = 'unpaid' (will be paid via encaissement)
                // For standalone orders: status = 'paid' (paid immediately)
                $vente = Vente::create([
                    'user_id' => SuperAdmin::databaseUserId(),
                    'table_id' => $request->table_id,
                    'total' => $total,
                    'payment_method' => $request->payment_method,
                    'status' => $isTableOrder ? 'unpaid' : 'paid',
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
                
                $message = $isTableOrder ? 'Commande créée pour la table.' : 'Vente enregistrée avec succès';
            }

            $redirectToTables = false;

            if ($isTableOrder) {
                // TABLE ORDER: Occupy table if not already, DO NOT create payment yet
                $table = Table::find($request->table_id);
                if ($table) {
                    if (!$table->isOccupied()) {
                        $table->occupy($vente, Auth::user());
                    }
                    $redirectToTables = true;
                }
            } else {
                // STANDALONE ORDER: Create payment immediately (only for new ventes)
                if (!$existingVente) {
                    Paiement::create([
                        'vente_id' => $vente->id,
                        'amount' => $total,
                        'method' => $request->payment_method,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'vente_id' => $vente->id,
                'total' => (float) $vente->total,
                'change' => !$isTableOrder && $request->amount_received ? (float) ($request->amount_received - $vente->total) : 0.0,
                'redirect_to_tables' => $redirectToTables,
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

<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Produit;
use App\Models\Category;
use App\Models\Commande;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaiterController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show waiter dashboard (tablet interface).
     */
    public function index()
    {
        $tables = Table::orderBy('id')->get();
        
        return view('waiter.index', compact('tables'));
    }

    /**
     * Show table order interface.
     */
    public function showTableOrder(Table $table)
    {
        $categories = Category::active()->with('produits')->get();
        $products = Produit::active()->get();
        
        return view('waiter.order', compact('table', 'categories', 'products'));
    }

    /**
     * Store a new kitchen order.
     */
    public function storeOrder(Request $request, Table $table)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
            'waiter_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $commande = $this->orderService->createKitchenOrder(
                $table,
                $validated['items'],
                $validated['waiter_notes'] ?? null
            );

            if ((int) $commande->table_id !== (int) $table->id) {
                DB::table('commandes')
                    ->where('id', $commande->id)
                    ->update(['table_id' => $table->id]);

                $commande->refresh();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commande créée avec succès',
                    'commande' => $commande,
                ]);
            }

            return redirect()
                ->route('waiter.index')
                ->with('success', "Commande créée pour la table {$table->numero}");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Show order details.
     */
    public function showOrder(Commande $commande)
    {
        // Ensure this is a kitchen order
        if (!$commande->isKitchenOrder()) {
            abort(404);
        }

        // Only show orders created by this waiter or admin
        if (Auth::user()->role !== 'admin' && $commande->user_id !== Auth::id()) {
            abort(403);
        }

        $commande->load(['details.produit', 'table', 'user']);
        
        return view('waiter.show', compact('commande'));
    }

    /**
     * Show waiter's orders history.
     */
    public function myOrders()
    {
        $orders = Commande::kitchen()
            ->where('user_id', Auth::id())
            ->with(['details.produit', 'table'])
            ->latest()
            ->paginate(20);
        
        return view('waiter.orders', compact('orders'));
    }

    /**
     * Get products by category (AJAX).
     */
    public function getProductsByCategory(Category $category)
    {
        $products = $category->produits()
            ->active()
            ->select('id', 'name', 'price_vente', 'stock_quantity', 'category_id')
            ->get();

        return response()->json($products);
    }

    /**
     * Check table availability.
     */
    public function checkTable(Table $table)
    {
        return response()->json([
            'available' => $table->isAvailable(),
            'status' => $table->status,
            'current_order' => $table->status === 'occupée' 
                ? $table->ventes()->latest()->first() 
                : null,
        ]);
    }
}

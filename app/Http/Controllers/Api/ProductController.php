<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Produit::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('barcode', $request->search);
            });
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'stock_alert_threshold');
        }

        $products = $query->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:50|unique:produits,barcode',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,archived',
            'kitchen_active' => 'nullable|boolean',
        ]);

        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $product = Produit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produit créé avec succès.',
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Produit $product)
    {
        $product->load(['category', 'stockMovements' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:50|unique:produits,barcode,' . $product->id,
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,archived',
            'kitchen_active' => 'nullable|boolean',
        ]);

        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour avec succès.',
            'data' => $product->load('category'),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Produit $product)
    {
        // Check if product has sales
        if ($product->venteDetails()->exists()) {
            // Archive instead of delete
            $product->update(['status' => 'archived']);

            return response()->json([
                'success' => true,
                'message' => 'Le produit a été archivé car il a des ventes associées.',
            ]);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès.',
        ]);
    }

    /**
     * Search products by barcode.
     */
    public function barcode(string $barcode)
    {
        $product = Produit::where('barcode', $barcode)
            ->active()
            ->with('category')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Get products for POS by category.
     */
    public function byCategory(Category $category)
    {
        $products = $category->produits()
            ->active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get low stock products.
     */
    public function lowStock()
    {
        $products = Produit::whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->with('category')
            ->orderBy('stock_quantity')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}

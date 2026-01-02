<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Get stock movements list.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['produit', 'user']);

        if ($request->filled('product_id')) {
            $query->where('produit_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    /**
     * Adjust stock for a product.
     */
    public function adjust(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,remove,adjustment',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $movement = $this->stockService->adjustStock(
                $product,
                $validated['quantity'],
                $validated['type'],
                $validated['reason']
            );

            $product->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Stock ajusté avec succès.',
                'data' => [
                    'movement' => $movement,
                    'product' => $product,
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
     * Add stock to a product.
     */
    public function add(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = $this->stockService->addStock(
            $product,
            $validated['quantity'],
            'manual_add',
            $validated['reason'] ?? 'Ajout manuel'
        );

        $product->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Stock ajouté avec succès.',
            'data' => [
                'movement' => $movement,
                'product' => $product,
            ],
        ]);
    }

    /**
     * Remove stock from a product.
     */
    public function remove(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $movement = $this->stockService->removeStock(
                $product,
                $validated['quantity'],
                'manual_remove',
                $validated['reason'] ?? 'Retrait manuel'
            );

            $product->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Stock retiré avec succès.',
                'data' => [
                    'movement' => $movement,
                    'product' => $product,
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
     * Bulk adjust stock for multiple products.
     */
    public function bulkAdjust(Request $request)
    {
        $validated = $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:produits,id',
            'adjustments.*.quantity' => 'required|integer',
            'adjustments.*.type' => 'required|in:add,remove,adjustment',
            'adjustments.*.reason' => 'nullable|string|max:500',
        ]);

        try {
            $results = $this->stockService->bulkAdjust($validated['adjustments']);

            return response()->json([
                'success' => true,
                'message' => 'Ajustements de stock effectués.',
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get low stock products.
     */
    public function lowStock()
    {
        $products = $this->stockService->getLowStockProducts();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get stock statistics.
     */
    public function stats()
    {
        $stats = $this->stockService->getStockStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get stock history for a product.
     */
    public function history(Request $request, Produit $product)
    {
        $movements = StockMovement::where('produit_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'movements' => $movements,
            ],
        ]);
    }

    /**
     * Get inventory valuation.
     */
    public function valuation()
    {
        $products = Produit::with('category')
            ->where('status', 'active')
            ->get();

        $totalCostValue = 0;
        $totalRetailValue = 0;

        $items = $products->map(function ($product) use (&$totalCostValue, &$totalRetailValue) {
            $costValue = $product->stock_quantity * ($product->cost_price ?? $product->price);
            $retailValue = $product->stock_quantity * $product->price;

            $totalCostValue += $costValue;
            $totalRetailValue += $retailValue;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? 'N/A',
                'stock_quantity' => $product->stock_quantity,
                'cost_price' => $product->cost_price,
                'retail_price' => $product->price,
                'cost_value' => $costValue,
                'retail_value' => $retailValue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_cost_value' => $totalCostValue,
                    'total_retail_value' => $totalRetailValue,
                    'potential_profit' => $totalRetailValue - $totalCostValue,
                ],
                'items' => $items,
            ],
        ]);
    }
}

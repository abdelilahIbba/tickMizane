<?php

namespace App\Services;

use App\Models\Produit;
use App\Models\StockMovement;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class StockService
{
    /**
     * Adjust stock for a product (add or remove).
     */
    public function adjustStock(
        Produit $produit,
        int $quantity,
        string $type,
        string $reason,
        ?int $referenceId = null
    ): StockMovement {
        return DB::transaction(function () use ($produit, $quantity, $type, $reason, $referenceId) {
            // Create stock movement record
            $movement = StockMovement::create([
                'produit_id' => $produit->id,
                'type' => $type,
                'quantity' => abs($quantity),
                'reason' => $reason,
                'reference_id' => $referenceId,
            ]);

            // Update product stock
            if ($type === 'in') {
                $produit->increment('stock_quantity', abs($quantity));
            } else {
                $produit->decrement('stock_quantity', abs($quantity));
            }

            // Refresh model to get updated stock
            $produit->refresh();

            // Check for low stock alert
            $this->checkLowStockAlert($produit);

            return $movement;
        });
    }

    /**
     * Add stock to a product.
     */
    public function addStock(
        Produit $produit,
        int $quantity,
        string $reason = 'ajustement',
        ?int $referenceId = null
    ): StockMovement {
        return $this->adjustStock($produit, $quantity, 'in', $reason, $referenceId);
    }

    /**
     * Remove stock from a product.
     */
    public function removeStock(
        Produit $produit,
        int $quantity,
        string $reason = 'ajustement',
        ?int $referenceId = null
    ): StockMovement {
        if ($produit->stock_quantity < $quantity) {
            throw new \InvalidArgumentException(
                "Stock insuffisant. Disponible: {$produit->stock_quantity}, Demandé: {$quantity}"
            );
        }

        return $this->adjustStock($produit, $quantity, 'out', $reason, $referenceId);
    }

    /**
     * Reduce stock from a product (alias for removeStock for waiter orders).
     */
    public function reduceStock(
        Produit $produit,
        int $quantity,
        string $reason = 'vente',
        ?int $referenceId = null
    ): StockMovement {
        return $this->removeStock($produit, $quantity, $reason, $referenceId);
    }

    /**
     * Check if product is low on stock and trigger alerts.
     */
    public function checkLowStockAlert(Produit $produit): bool
    {
        if ($produit->isLowStock()) {
            // Get admin users to notify
            $admins = \App\Models\User::where('role', 'admin')
                ->where('status', 'active')
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new LowStockNotification($produit));
            }

            return true;
        }

        return false;
    }

    /**
     * Get all products with low stock.
     */
    public function getLowStockProducts(): Collection
    {
        return Produit::active()
            ->lowStock()
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Get stock movement history for a product.
     */
    public function getProductMovements(Produit $produit, int $limit = 50): Collection
    {
        return $produit->stockMovements()
            ->with('produit')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock value (total inventory value).
     */
    public function getStockValue(): float
    {
        return Produit::active()
            ->selectRaw('SUM(stock_quantity * price_achat) as total_value')
            ->value('total_value') ?? 0;
    }

    /**
     * Get stock statistics.
     */
    public function getStockStats(): array
    {
        $totalProducts = Produit::active()->count();
        $lowStockCount = Produit::active()->lowStock()->count();
        $outOfStock = Produit::active()->where('stock_quantity', 0)->count();

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStock,
            'healthy_stock_count' => $totalProducts - $lowStockCount - $outOfStock,
            'stock_value' => $this->getStockValue(),
            'total_movements_today' => StockMovement::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Bulk stock adjustment.
     */
    public function bulkAdjust(array $adjustments): array
    {
        $results = [];

        DB::transaction(function () use ($adjustments, &$results) {
            foreach ($adjustments as $adjustment) {
                $produit = Produit::findOrFail($adjustment['produit_id']);
                $results[] = $this->adjustStock(
                    $produit,
                    $adjustment['quantity'],
                    $adjustment['type'],
                    $adjustment['reason'] ?? 'ajustement',
                    $adjustment['reference_id'] ?? null
                );
            }
        });

        return $results;
    }
}

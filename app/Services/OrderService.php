<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new supplier order.
     */
    public function createOrder(Fournisseur $fournisseur, array $items): Commande
    {
        return DB::transaction(function () use ($fournisseur, $items) {
            // Create commande
            $commande = Commande::create([
                'fournisseur_id' => $fournisseur->id,
                'user_id' => Auth::id(),
                'total' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            // Create commande details
            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);

                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? $produit->price_achat ?? 0,
                ]);

                $total += ($item['price'] ?? $produit->price_achat ?? 0) * $item['quantity'];
            }

            // Update total
            $commande->update(['total' => $total]);

            return $commande->fresh(['details.produit', 'fournisseur']);
        });
    }

    /**
     * Update an existing order.
     */
    public function updateOrder(Commande $commande, array $items): Commande
    {
        if ($commande->isReceived()) {
            throw new \InvalidArgumentException('Impossible de modifier une commande déjà reçue.');
        }

        return DB::transaction(function () use ($commande, $items) {
            // Delete existing details
            $commande->details()->delete();

            $total = 0;

            // Create new details
            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);

                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? $produit->price_achat ?? 0,
                ]);

                $total += ($item['price'] ?? $produit->price_achat ?? 0) * $item['quantity'];
            }

            // Update total
            $commande->update(['total' => $total]);

            return $commande->fresh(['details.produit', 'fournisseur']);
        });
    }

    /**
     * Mark an order as received and update stock.
     */
    public function markReceived(Commande $commande): Commande
    {
        if ($commande->isReceived()) {
            throw new \InvalidArgumentException('Cette commande est déjà marquée comme reçue.');
        }

        return DB::transaction(function () use ($commande) {
            // Update stock for each product
            foreach ($commande->details as $detail) {
                $this->stockService->addStock(
                    $detail->produit,
                    $detail->quantity,
                    'commande',
                    $commande->id
                );
            }

            // Mark commande as received
            $commande->update(['status' => 'received']);

            $commande->logCustomAction('receive', "Commande #{$commande->id} reçue et stock mis à jour");

            return $commande->fresh();
        });
    }

    /**
     * Cancel a pending order.
     */
    public function cancelOrder(Commande $commande): void
    {
        if ($commande->isReceived()) {
            throw new \InvalidArgumentException('Impossible d\'annuler une commande déjà reçue.');
        }

        $commande->delete();
    }

    /**
     * Get pending orders.
     */
    public function getPendingOrders(): Collection
    {
        return Commande::pending()
            ->with(['fournisseur', 'user', 'details.produit'])
            ->latest()
            ->get();
    }

    /**
     * Get orders by fournisseur.
     */
    public function getOrdersByFournisseur(Fournisseur $fournisseur): Collection
    {
        return $fournisseur->commandes()
            ->with(['user', 'details.produit'])
            ->latest()
            ->get();
    }

    /**
     * Get order statistics.
     */
    public function getOrderStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Commande::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->get();

        return [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'received_orders' => $orders->where('status', 'received')->count(),
            'total_value' => $orders->sum('total'),
            'pending_value' => $orders->where('status', 'pending')->sum('total'),
            'received_value' => $orders->where('status', 'received')->sum('total'),
        ];
    }

    /**
     * Calculate order total.
     */
    public function calculateTotal(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $produit = Produit::find($item['produit_id']);
            $price = $item['price'] ?? ($produit?->price_achat ?? 0);
            $total += $price * $item['quantity'];
        }

        return $total;
    }

    /**
     * Get suggested reorder products (products that need restocking).
     */
    public function getSuggestedReorders(): Collection
    {
        return Produit::active()
            ->lowStock()
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->map(function ($produit) {
                return [
                    'produit' => $produit,
                    'current_stock' => $produit->stock_quantity,
                    'alert_level' => $produit->alert_stock,
                    'suggested_quantity' => max(($produit->alert_stock * 2) - $produit->stock_quantity, $produit->alert_stock),
                ];
            });
    }
}

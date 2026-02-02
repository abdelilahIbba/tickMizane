<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Table;
use App\Events\NewKitchenOrder;
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
                'type' => 'supplier',
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

    /**
     * Create a kitchen order from waiter tablet.
     */
    public function createKitchenOrder(Table $table, array $items, ?string $waiterNotes = null): Commande
    {
        return DB::transaction(function () use ($table, $items, $waiterNotes) {
            // Create kitchen order
            $commande = Commande::create([
                'user_id' => Auth::id(),
                'table_id' => $table->id,
                'total' => 0,
                'status' => 'en_preparation',
                'type' => 'kitchen',
                'waiter_notes' => $waiterNotes,
            ]);

            $total = 0;

            // Create commande details with notes
            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);

                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantity' => $item['quantity'],
                    'price' => $produit->price_vente,
                    'notes' => $item['notes'] ?? null,
                ]);

                $total += $produit->price_vente * $item['quantity'];

                // Deduct stock
                $this->stockService->reduceStock(
                    $produit,
                    $item['quantity'],
                    'vente',
                    $commande->id
                );
            }

            // Update total
            $commande->update(['total' => $total]);

            // Update table status to occupied
            $table->update(['status' => 'occupied']);

            // Fire event for kitchen notification
            event(new NewKitchenOrder($commande));

            return $commande->fresh(['details.produit', 'table', 'user']);
        });
    }

    /**
     * Update kitchen order status.
     */
    public function updateKitchenOrderStatus(Commande $commande, string $status): Commande
    {
        if (!$commande->isKitchenOrder()) {
            throw new \InvalidArgumentException('Cette commande n\'est pas une commande cuisine.');
        }

        $validStatuses = ['en_preparation', 'servi', 'annule'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Statut invalide.');
        }

        $commande->update(['status' => $status]);

        // If order is served, free the table
        if ($status === 'servi' && $commande->table) {
            $commande->table->update(['status' => 'free']);
        }

        return $commande->fresh();
    }

    /**
     * Get kitchen orders (orders in preparation).
     */
    public function getKitchenOrders(): Collection
    {
        return Commande::kitchen()
            ->whereIn('status', ['en_preparation', 'servi'])
            ->with(['details.produit', 'table', 'user'])
            ->latest()
            ->get();
    }

    /**
     * Get active kitchen orders (in preparation only).
     */
    public function getActiveKitchenOrders(): Collection
    {
        return Commande::kitchen()
            ->enPreparation()
            ->with(['details.produit', 'table', 'user'])
            ->oldest()
            ->get();
    }

    /**
     * Generate kitchen ticket data.
     */
    public function generateKitchenTicket(Commande $commande): array
    {
        if (!$commande->isKitchenOrder()) {
            throw new \InvalidArgumentException('Cette commande n\'est pas une commande cuisine.');
        }

        return [
            'order_id' => $commande->id,
            'table_number' => $commande->table?->numero ?? 'N/A',
            'table_name' => $commande->table?->name ?? 'Non assignée',
            'waiter_name' => $commande->user?->name ?? 'Système',
            'waiter_notes' => $commande->waiter_notes,
            'items' => $commande->details->map(function ($detail) {
                return [
                    'product_name' => $detail->produit->name,
                    'quantity' => $detail->quantity,
                    'notes' => $detail->notes,
                ];
            }),
            'created_at' => $commande->created_at,
            'status' => $commande->status,
        ];
    }
}

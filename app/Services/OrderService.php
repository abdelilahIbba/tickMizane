<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Table;
use App\Events\NewKitchenOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
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
            $hasKitchenItems = false;
            $resolvedItems = [];

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $resolvedItems[] = [$item, $produit];

                if ($produit->isKitchenActive()) {
                    $hasKitchenItems = true;
                }
            }

            // Create kitchen order with 'en_cuisine' status
            $commande = Commande::create([
                'user_id' => Auth::id(),
                'table_id' => $table->id,
                'total' => 0,
                'status' => $hasKitchenItems ? 'en_cuisine' : 'pret', // Order sent to kitchen unless everything is direct service
                'type' => 'kitchen',
                'waiter_notes' => $waiterNotes,
            ]);

            if ((int) $commande->table_id !== (int) $table->id) {
                $commande->forceFill(['table_id' => $table->id])->save();
            }

            $total = 0;

            // Create commande details with notes
            foreach ($resolvedItems as [$item, $produit]) {
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

            if (!$hasKitchenItems) {
                $commande->update(['ready_at' => now()]);
            }

            // Update table status to occupied
            $table->update(['status' => 'occupied']);

            // Fire event for kitchen notification only when kitchen prep is needed
            if ($hasKitchenItems) {
                event(new NewKitchenOrder($commande));
            }

            return $commande->fresh(['details.produit', 'table', 'user']);
        });
    }

    /**
     * Add new items to an existing kitchen order.
     */
    public function addItemsToKitchenOrder(Commande $commande, array $items, ?string $waiterNotes = null): Commande
    {
        return DB::transaction(function () use ($commande, $items, $waiterNotes) {
            $hasKitchenItems = false;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);

                if ($produit->isKitchenActive()) {
                    $hasKitchenItems = true;
                }

                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id'  => $produit->id,
                    'quantity'    => $item['quantity'],
                    'price'       => $produit->price_vente,
                    'notes'       => $item['notes'] ?? null,
                ]);

                $this->stockService->reduceStock($produit, $item['quantity'], 'vente', $commande->id);
            }

            if ($waiterNotes) {
                $commande->update(['waiter_notes' => $waiterNotes]);
            }

            $commande->recalculateTotal();

            // Fire kitchen event so kitchen screen refreshes
            if ($hasKitchenItems) {
                event(new NewKitchenOrder($commande->fresh()));
            }

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

        $validStatuses = ['en_cuisine', 'en_preparation', 'pret', 'servi', 'payee', 'annule'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Statut invalide.');
        }

        $oldStatus = $commande->status;
        
        $data = ['status' => $status];
        
        // Set validated_at timestamp when starting preparation
        if ($status === 'en_preparation' && $oldStatus === 'en_cuisine') {
            $data['validated_at'] = now();
        }

        // Set ready_at timestamp when marking as ready
        if ($status === 'pret' && $oldStatus !== 'pret') {
            $data['ready_at'] = now();
        }

        $commande->update($data);

        // Log the status transition
        $commande->logCustomAction('status_change', "Statut modifié: {$oldStatus} → {$status}");

        // Table status logic
        if ($status === 'annule' && $commande->table) {
            // Check if table has other pending orders
            $otherOrders = Commande::where('table_id', $commande->table_id)
                ->where('id', '!=', $commande->id)
                ->where('type', 'kitchen')
                ->whereNotIn('status', ['payee', 'annule'])
                ->exists();
            
            if (!$otherOrders) {
                $commande->table()?->update(['status' => 'free']);
            }
        }

        return $commande->fresh();
    }

    /**
     * Get kitchen orders (all active kitchen orders).
     */
    public function getKitchenOrders(): Collection
    {
        return $this->filterOrdersWithKitchenItems(Commande::kitchen()
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
            ->with(['details.produit', 'table', 'user'])
            ->latest()
            ->get());
    }

    /**
     * Get active kitchen orders (in kitchen, in preparation, or ready).
     */
    public function getActiveKitchenOrders(): Collection
    {
        return $this->filterOrdersWithKitchenItems(Commande::kitchen()
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret'])
            ->with(['details.produit', 'table', 'user'])
            ->oldest()
            ->get());
    }

    /**
     * Get orders pending payment for cashier.
     * Staff orders appear immediately at any active status; client orders only when pret/servi.
     */
    public function getPendingPaymentOrders(): Collection
    {
        $orders = Commande::kitchen()
            ->forCashier()
            ->with(['details.produit', 'table', 'user'])
            ->oldest()
            ->get();

        return $this->buildPendingPaymentTableSummaries($orders);
    }

    /**
     * Get orders available for cashier (staff orders at any active status, client orders pret/servi).
     */
    public function getReadyForPaymentOrders(): Collection
    {
        return Commande::kitchen()
            ->forCashier()
            ->with(['details.produit', 'table', 'user'])
            ->oldest()
            ->get();
    }

    /**
     * Get all payable kitchen orders for a specific table payment session.
     */
    public function getReadyPaymentOrdersForTable(int $tableId): Collection
    {
        return Commande::kitchen()
            ->forCashier()
            ->where('table_id', $tableId)
            ->with(['details.produit', 'table', 'user'])
            ->oldest()
            ->get();
    }

    /**
     * Process payment for a kitchen order.
     */
    public function processKitchenOrderPayment(Commande $commande, string $paymentMethod, ?float $amountReceived = null): Commande
    {
        if (!$commande->isKitchenOrder()) {
            throw new \InvalidArgumentException('Cette commande n\'est pas une commande cuisine.');
        }

        if ($commande->isPaid()) {
            throw new \InvalidArgumentException('Cette commande est déjà payée.');
        }

        return DB::transaction(function () use ($commande, $paymentMethod, $amountReceived) {
            $total = $commande->total;
            $change = 0;

            if ($paymentMethod === 'cash' && $amountReceived !== null) {
                $change = max(0, $amountReceived - $total);
            }

            // Mark order as paid
            $commande->update(['status' => 'payee']);

            // Free the table if no other pending orders
            if ($commande->table) {
                $otherOrders = Commande::where('table_id', $commande->table_id)
                    ->where('id', '!=', $commande->id)
                    ->where('type', 'kitchen')
                    ->whereNotIn('status', ['payee', 'annule'])
                    ->exists();
                
                if (!$otherOrders) {
                    $commande->table()?->update(['status' => 'free']);
                }
            }

            // Log the payment
            $commande->logCustomAction('payment', "Paiement de {$total} DH via {$paymentMethod}");

            return $commande->fresh(['details.produit', 'table', 'user']);
        });
    }

    /**
     * Group ready kitchen orders into one cashier entry per table.
     */
    protected function buildPendingPaymentTableSummaries(Collection $orders): Collection
    {
        // Split: orders linked to a real table vs client orders (no table)
        $withTable    = $orders->filter(fn ($o) => $o->table_id !== null);
        $withoutTable = $orders->filter(fn ($o) => $o->table_id === null);

        // Group table orders by table_id (existing behaviour)
        $tableSummaries = $withTable
            ->groupBy('table_id')
            ->map(function (Collection $tableOrders) {
                /** @var \App\Models\Commande $firstOrder */
                $firstOrder = $tableOrders->sortBy('created_at')->first();
                $details = $tableOrders->flatMap(function (Commande $order) {
                    return $order->details->map(function ($detail) use ($order) {
                        $detail->source_commande_id = $order->id;
                        return $detail;
                    });
                })->values();

                $status = $tableOrders->contains(fn (Commande $order) => $order->status === 'servi') ? 'servi' : 'pret';

                return new Fluent([
                    'id'                          => $firstOrder->id,
                    'representative_commande_id'  => $firstOrder->id,
                    'table'                       => $firstOrder->table,
                    'user'                        => $firstOrder->user,
                    'user_names'                  => $tableOrders->pluck('user.name')->filter()->unique()->implode(', '),
                    'details'                     => $details,
                    'total'                       => (float) $tableOrders->sum(fn (Commande $order) => (float) $order->total),
                    'created_at'                  => $firstOrder->created_at,
                    'status'                      => $status,
                    'status_label'                => $tableOrders->count() > 1 ? 'Prêtes à payer' : $firstOrder->status_label,
                    'waiter_notes'                => $tableOrders->pluck('waiter_notes')->filter()->implode(' | '),
                    'ready_for_payment'           => true,
                    'orders_count'                => $tableOrders->count(),
                    'order_refs'                  => $tableOrders->pluck('id')->map(fn ($id) => 'Cmd #' . $id)->implode(', '),
                    'is_client_order'             => false,
                ]);
            })
            ->values();

        // Each client order (no table) becomes its own independent card
        $clientSummaries = $withoutTable->map(function (Commande $order) {
            $details = $order->details->map(function ($detail) use ($order) {
                $detail->source_commande_id = $order->id;
                return $detail;
            })->values();

            return new Fluent([
                'id'                          => $order->id,
                'representative_commande_id'  => $order->id,
                'table'                       => null,
                'user'                        => null,
                'user_names'                  => 'Commande client',
                'details'                     => $details,
                'total'                       => (float) $order->total,
                'created_at'                  => $order->created_at,
                'status'                      => $order->status,
                'status_label'                => $order->status_label,
                'waiter_notes'                => $order->waiter_notes,
                'ready_for_payment'           => true,
                'orders_count'                => 1,
                'order_refs'                  => 'Cmd #' . $order->id,
                'is_client_order'             => true,
            ]);
        })->values();

        return $tableSummaries->concat($clientSummaries);
    }

    /**
     * Generate kitchen ticket data.
     */
    public function generateKitchenTicket(Commande $commande): array
    {
        if (!$commande->isKitchenOrder()) {
            throw new \InvalidArgumentException('Cette commande n\'est pas une commande cuisine.');
        }

        $items = $commande->details
            ->filter(fn ($detail) => $detail->produit?->isKitchenActive())
            ->values()
            ->map(function ($detail) {
                return [
                    'product_name' => $detail->produit->name,
                    'quantity' => $detail->quantity,
                    'notes' => $detail->notes,
                ];
            });

        return [
            'order_id' => $commande->id,
            'table_number' => $commande->table?->numero ?? 'N/A',
            'table_name' => $commande->table?->name ?? 'Non assignée',
            'waiter_name' => $commande->user?->name ?? 'Système',
            'waiter_notes' => $commande->waiter_notes,
            'items' => $items,
            'created_at' => $commande->created_at,
            'status' => $commande->status,
        ];
    }

    /**
     * Keep only orders that contain at least one kitchen-active product and trim their visible details.
     */
    protected function filterOrdersWithKitchenItems(Collection $orders): Collection
    {
        return $orders
            ->filter(function (Commande $order) {
                return $order->details->contains(fn ($detail) => $detail->produit?->isKitchenActive());
            })
            ->values()
            ->map(function (Commande $order) {
                $order->setRelation(
                    'details',
                    $order->details->filter(fn ($detail) => $detail->produit?->isKitchenActive())->values()
                );

                return $order;
            });
    }
}

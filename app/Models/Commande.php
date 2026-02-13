<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commandes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fournisseur_id',
        'user_id',
        'table_id',
        'total',
        'status',
        'type',
        'waiter_notes',
        'validated_at',
        'ready_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'validated_at' => 'datetime',
            'ready_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the fournisseur for this commande.
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * Get the user who created this commande.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all details (line items) for this commande.
     */
    public function details(): HasMany
    {
        return $this->hasMany(CommandeDetail::class);
    }

    /**
     * Get the table for this commande (for kitchen orders).
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get all paiements for this commande.
     */
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only pending commandes.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to only received commandes.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope to only kitchen orders.
     */
    public function scopeKitchen($query)
    {
        return $query->where('type', 'kitchen');
    }

    /**
     * Scope to only supplier orders.
     */
    public function scopeSupplier($query)
    {
        return $query->where('type', 'supplier');
    }

    /**
     * Scope for orders sent to kitchen (waiting for preparation).
     */
    public function scopeEnCuisine($query)
    {
        return $query->where('status', 'en_cuisine');
    }

    /**
     * Scope for orders in preparation.
     */
    public function scopeEnPreparation($query)
    {
        return $query->where('status', 'en_preparation');
    }

    /**
     * Scope for orders ready (prêt).
     */
    public function scopePret($query)
    {
        return $query->where('status', 'pret');
    }

    /**
     * Scope for served orders.
     */
    public function scopeServi($query)
    {
        return $query->where('status', 'servi');
    }

    /**
     * Scope for paid orders.
     */
    public function scopePayee($query)
    {
        return $query->where('status', 'payee');
    }

    /**
     * Scope for orders pending payment (en_cuisine, en_preparation, pret, servi).
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pret')
                     ->where('type', 'kitchen');
    }

    /**
     * Scope for orders ready for cashier (pret or servi).
     */
    public function scopeReadyForPayment($query)
    {
        return $query->whereIn('status', ['pret', 'servi']);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's commandes.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope by table.
     */
    public function scopeByTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if commande is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if commande is received.
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Mark commande as received.
     */
    public function markReceived(): void
    {
        $this->update(['status' => 'received']);
    }

    /**
     * Mark order as in preparation (kitchen).
     */
    public function markEnPreparation(): void
    {
        $this->update(['status' => 'en_preparation']);
    }

    /**
     * Mark order as ready (prêt).
     */
    public function markPret(): void
    {
        $this->update(['status' => 'pret']);
    }

    /**
     * Mark order as served.
     */
    public function markServi(): void
    {
        $this->update(['status' => 'servi']);
    }

    /**
     * Mark order as paid.
     */
    public function markPayee(): void
    {
        $this->update(['status' => 'payee']);
    }

    /**
     * Check if this is a kitchen order.
     */
    public function isKitchenOrder(): bool
    {
        return $this->type === 'kitchen';
    }

    /**
     * Alias for isKitchenOrder().
     */
    public function isKitchen(): bool
    {
        return $this->isKitchenOrder();
    }

    /**
     * Check if order is ready.
     */
    public function isReady(): bool
    {
        return $this->status === 'pret';
    }

    /**
     * Check if order is pending payment.
     */
    public function isPendingPayment(): bool
    {
        return in_array($this->status, ['en_cuisine', 'en_preparation', 'pret', 'servi']);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'payee';
    }

    /**
     * Check if order is ready for payment.
     */
    public function isReadyForPayment(): bool
    {
        return in_array($this->status, ['pret', 'servi']);
    }

    /**
     * Get status label in French.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'received' => 'Reçue',
            'en_cuisine' => 'En cuisine',
            'en_preparation' => 'En préparation',
            'pret' => 'Prêt',
            'servi' => 'Servi',
            'payee' => 'Payée',
            'annule' => 'Annulée',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'received' => 'green',
            'en_cuisine' => 'orange',
            'en_preparation' => 'blue',
            'pret' => 'emerald',
            'servi' => 'cyan',
            'payee' => 'green',
            'annule' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if this is a supplier order.
     */
    public function isSupplierOrder(): bool
    {
        return $this->type === 'supplier';
    }

    /**
     * Calculate and update total from details.
     */
    public function recalculateTotal(): void
    {
        $total = $this->details()->selectRaw('SUM(quantity * price) as total')->value('total') ?? 0;
        $this->update(['total' => $total]);
    }

    /**
     * Get historique identifier.
     */
    protected function getHistoriqueIdentifier(): string
    {
        return 'CMD-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}

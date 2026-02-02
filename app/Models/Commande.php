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
     * Scope for orders in preparation.
     */
    public function scopeEnPreparation($query)
    {
        return $query->where('status', 'en_preparation');
    }

    /**
     * Scope for served orders.
     */
    public function scopeServi($query)
    {
        return $query->where('status', 'servi');
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
     * Mark order as served.
     */
    public function markServi(): void
    {
        $this->update(['status' => 'servi']);
    }

    /**
     * Check if this is a kitchen order.
     */
    public function isKitchenOrder(): bool
    {
        return $this->type === 'kitchen';
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

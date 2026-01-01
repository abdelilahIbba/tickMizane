<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_movements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'produit_id',
        'type',
        'quantity',
        'reason',
        'reference_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reference_id' => 'integer',
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
     * Get the produit for this movement.
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only 'in' movements.
     */
    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope to only 'out' movements.
     */
    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope by reason.
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if this is an 'in' movement.
     */
    public function isIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if this is an 'out' movement.
     */
    public function isOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Get historique identifier.
     */
    protected function getHistoriqueIdentifier(): string
    {
        $produitName = $this->produit?->name ?? 'Produit';
        $direction = $this->type === 'in' ? '+' : '-';
        return "{$produitName} {$direction}{$this->quantity}";
    }

    /**
     * Get reference label based on reason.
     */
    public function getReferenceLabel(): string
    {
        if (!$this->reference_id) {
            return '-';
        }

        return match($this->reason) {
            'vente' => 'VENTE-' . str_pad($this->reference_id, 4, '0', STR_PAD_LEFT),
            'commande' => 'CMD-' . str_pad($this->reference_id, 4, '0', STR_PAD_LEFT),
            'perte' => 'PERTE-' . str_pad($this->reference_id, 3, '0', STR_PAD_LEFT),
            default => 'ADJ-' . str_pad($this->reference_id, 3, '0', STR_PAD_LEFT),
        };
    }
}

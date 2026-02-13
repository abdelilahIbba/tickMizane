<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'produits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'price_vente',
        'price_achat',
        'stock_quantity',
        'alert_stock',
        'unit',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_vente' => 'decimal:2',
            'price_achat' => 'decimal:2',
            'stock_quantity' => 'integer',
            'alert_stock' => 'integer',
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
     * Get the category this produit belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all vente details for this produit.
     */
    public function venteDetails(): HasMany
    {
        return $this->hasMany(VenteDetail::class);
    }

    /**
     * Get all commande details for this produit.
     */
    public function commandeDetails(): HasMany
    {
        return $this->hasMany(CommandeDetail::class);
    }

    /**
     * Get all stock movements for this produit.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only active produits.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to produits with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'alert_stock');
    }

    /**
     * Scope to produits in stock (quantity > 0).
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to produits out of stock.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope to search produits by name or description.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for sellable products (active and in stock).
     */
    public function scopeSellable($query)
    {
        return $query->active()->inStock();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if stock is below alert threshold.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_stock;
    }

    /**
     * Decrement stock quantity.
     */
    public function decrementStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Increment stock quantity.
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }
}

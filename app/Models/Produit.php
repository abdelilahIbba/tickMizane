<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'image',
        'price_vente',
        'price_achat',
        'stock_quantity',
        'alert_stock',
        'unit',
        'status',
        'kitchen_active',
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
            'kitchen_active' => 'boolean',
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

    /**
     * Scope to products that should go through the kitchen.
     */
    public function scopeKitchenActive($query)
    {
        return $query->where('kitchen_active', true);
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
     * Check whether the product should be prepared by the kitchen.
     */
    public function isKitchenActive(): bool
    {
        return (bool) $this->kitchen_active;
    }

    /**
     * Decrement stock quantity.
     */
    public function decrementStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity, []);
    }

    /**
     * Increment stock quantity.
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity, []);
    }

    /**
     * Get normalized image URL (external URL or local storage asset).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    /**
     * Get a stable display image URL with locked real-photo fallbacks.
     */
    public function getDisplayImageUrlAttribute(): string
    {
        $current = $this->image_url;

        if (!empty($current) && !Str::contains($current, 'source.unsplash.com')) {
            return $current;
        }

        $fallbacks = [
            Str::lower('Eau minérale 1.5L') => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?auto=format&fit=crop&w=640&q=80',
            Str::lower('Coca-Cola 33cl') => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=640&q=80',
            Str::lower('Café') => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=640&q=80',
            Str::lower('Chips Lays 150g') => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=640&q=80',
            Str::lower('Pain de mie') => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=640&q=80',
            Str::lower('Lait 1L') => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=640&q=80',
        ];

        $key = Str::lower(trim((string) $this->name));
        if (isset($fallbacks[$key])) {
            return $fallbacks[$key];
        }

        return 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=640&q=80';
    }
}

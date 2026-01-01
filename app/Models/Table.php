<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
     * Get all ventes for this table.
     */
    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only free tables.
     */
    public function scopeFree($query)
    {
        return $query->where('status', 'free');
    }

    /**
     * Scope to only occupied tables.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if table is free.
     */
    public function isFree(): bool
    {
        return $this->status === 'free';
    }

    /**
     * Check if table is occupied.
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    /**
     * Mark table as occupied.
     */
    public function markOccupied(): void
    {
        $this->update(['status' => 'occupied']);
    }

    /**
     * Mark table as free.
     */
    public function markFree(): void
    {
        $this->update(['status' => 'free']);
    }
}

<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'prefix',
        'description',
        'tables_count',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tables_count' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get tables assigned to this zone.
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Scope only active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

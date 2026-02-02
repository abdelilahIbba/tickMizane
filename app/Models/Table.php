<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'places',
        'zone',
        'status',
        'current_vente_id',
        'serveur_id',
        'occupied_at',
        'notes',
        'is_active',
        'qr_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'places' => 'integer',
            'occupied_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            if (empty($table->qr_code)) {
                $table->qr_code = 'TBL-' . Str::upper(Str::random(8));
            }
        });
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

    /**
     * Get the current active vente.
     */
    public function currentVente(): BelongsTo
    {
        return $this->belongsTo(Vente::class, 'current_vente_id');
    }

    /**
     * Get the assigned serveur (waiter).
     */
    public function serveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'serveur_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only active tables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

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

    /**
     * Scope by zone.
     */
    public function scopeInZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope by number of places.
     */
    public function scopeWithMinPlaces($query, int $places)
    {
        return $query->where('places', '>=', $places);
    }

    /**
     * Scope by serveur.
     */
    public function scopeForServeur($query, int $serveurId)
    {
        return $query->where('serveur_id', $serveurId);
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
     * Check if table is available (alias for isFree for French compatibility).
     */
    public function isAvailable(): bool
    {
        return $this->isFree();
    }

    /**
     * Check if table is occupied.
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if table is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Occupy the table with a vente.
     */
    public function occupy(?Vente $vente = null, ?User $serveur = null): self
    {
        $this->update([
            'status' => 'occupied',
            'current_vente_id' => $vente?->id,
            'serveur_id' => $serveur?->id ?? $this->serveur_id,
            'occupied_at' => now(),
        ]);

        return $this;
    }

    /**
     * Release the table (mark as free).
     */
    public function release(): self
    {
        $this->update([
            'status' => 'free',
            'current_vente_id' => null,
            'serveur_id' => null,
            'occupied_at' => null,
        ]);

        return $this;
    }

    /**
     * Alias for release() - Mark table as free.
     */
    public function markFree(): self
    {
        return $this->release();
    }

    /**
     * Assign a serveur to the table.
     */
    public function assignServeur(User $serveur): self
    {
        $this->update(['serveur_id' => $serveur->id]);
        return $this;
    }

    /**
     * Get the current bill amount.
     */
    public function getCurrentBillAmount(): float
    {
        if (!$this->current_vente_id) {
            return 0;
        }

        return $this->currentVente?->total ?? 0;
    }

    /**
     * Get time occupied in minutes.
     */
    public function getOccupiedMinutes(): ?int
    {
        if (!$this->occupied_at) {
            return null;
        }

        return $this->occupied_at->diffInMinutes(now());
    }

    /**
     * Get formatted occupation time.
     */
    public function getOccupiedTimeFormatted(): ?string
    {
        $minutes = $this->getOccupiedMinutes();
        
        if ($minutes === null) {
            return null;
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . 'h' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . 'min' : '');
    }

    /**
     * Get zone display name.
     */
    public function getZoneDisplayName(): string
    {
        return match($this->zone) {
            'interieur' => 'Intérieur',
            'terrasse' => 'Terrasse',
            'salon' => 'Salon privé',
            'bar' => 'Bar',
            default => $this->zone ?? 'Non défini',
        };
    }

    /**
     * Get the historique identifier for logging.
     */
    public function getHistoriqueIdentifier(): string
    {
        return $this->name;
    }

    /**
     * Get available zones.
     */
    public static function getZones(): array
    {
        return [
            'interieur' => 'Intérieur',
            'terrasse' => 'Terrasse',
            'salon' => 'Salon privé',
            'bar' => 'Bar',
        ];
    }

    /**
     * Get available places options.
     */
    public static function getPlacesOptions(): array
    {
        return [
            2 => '2 places',
            4 => '4 places',
            6 => '6 places',
            8 => '8 places',
            10 => '10 places',
            12 => '12 places',
        ];
    }

    /**
     * Get table number (accessor for compatibility).
     */
    public function getNumeroAttribute(): int
    {
        return $this->id;
    }

    /**
     * Get capacity (accessor for compatibility).
     */
    public function getCapacityAttribute(): int
    {
        return $this->places ?? 4;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class License extends Model
{
    use HasFactory;

    public const PERIOD_1_WEEK = '1_week';
    public const PERIOD_2_WEEKS = '2_weeks';
    public const PERIOD_1_MONTH = '1_month';
    public const PERIOD_LIFETIME = 'lifetime';

    public const STATUS_CREATED = 'created';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'client_name',
        'period',
        'status',
        'is_activated',
        'activated_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_activated' => 'boolean',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function periods(): array
    {
        return [
            self::PERIOD_1_WEEK => '1 semaine',
            self::PERIOD_2_WEEKS => '2 semaines',
            self::PERIOD_1_MONTH => '1 mois',
            self::PERIOD_LIFETIME => 'À vie (Lifetime)',
        ];
    }

    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query
            ->where('is_activated', true)
            ->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $query) {
                $query
                    ->where('period', self::PERIOD_LIFETIME)
                    ->orWhere(function (Builder $query) {
                        $query
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '>', now());
                    });
            });
    }

    public function isCurrentlyValid(): bool
    {
        if (!$this->is_activated || $this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return $this->isLifetime()
            || ($this->expires_at instanceof Carbon && $this->expires_at->isFuture());
    }

    public function isLifetime(): bool
    {
        return $this->period === self::PERIOD_LIFETIME;
    }

    public function periodLabel(): string
    {
        return self::periods()[$this->period] ?? $this->period;
    }
}

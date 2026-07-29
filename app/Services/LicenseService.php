<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LicenseService
{
    private const CACHE_KEY = 'license:current_valid';

    public function periods(): array
    {
        return License::periods();
    }

    public function current(): ?License
    {
        return Cache::remember(self::CACHE_KEY, 60, function () {
            return License::query()
                ->currentlyValid()
                ->orderByDesc('activated_at')
                ->first();
        });
    }

    public function hasActiveLicense(): bool
    {
        return $this->current() !== null;
    }

    public function isExpiredOrMissing(): bool
    {
        return !$this->hasActiveLicense();
    }

    /**
     * Client-facing message shown when the installation has no usable license.
     */
    public function clientBlockMessage(): string
    {
        if ($this->latestExpired()) {
            return 'Votre période d\'essai / licence a expiré. Le système est bloqué. Contactez DevNApp pour régler et prolonger l\'accès.';
        }

        return 'Aucune licence active. Le système est bloqué. Contactez DevNApp pour activer une période d\'essai ou régler une licence.';
    }

    public function latestExpired(): ?License
    {
        return License::query()
            ->where('status', License::STATUS_EXPIRED)
            ->whereNotNull('expires_at')
            ->orderByDesc('expires_at')
            ->first();
    }

    public function create(string $clientName, string $period, ?string $notes = null): License
    {
        $this->assertValidPeriod($period);

        $license = License::create([
            'client_name' => $clientName,
            'period' => $period,
            'status' => License::STATUS_CREATED,
            'is_activated' => false,
            'activated_at' => null,
            'expires_at' => null,
            'notes' => $notes,
        ]);

        $this->forgetCache();

        return $license;
    }

    public function activate(License $license): License
    {
        if ($license->status === License::STATUS_REVOKED) {
            throw new InvalidArgumentException('Impossible d\'activer une licence révoquée.');
        }

        if ($license->is_activated && $license->isCurrentlyValid()) {
            return $license;
        }

        $this->assertValidPeriod($license->period);

        return DB::transaction(function () use ($license) {
            License::query()
                ->where('status', License::STATUS_ACTIVE)
                ->where('id', '!=', $license->id)
                ->update([
                    'status' => License::STATUS_EXPIRED,
                    'is_activated' => false,
                ]);

            $activatedAt = now();
            $license->update([
                'is_activated' => true,
                'status' => License::STATUS_ACTIVE,
                'activated_at' => $activatedAt,
                'expires_at' => $this->expiresAtForPeriod($license->period, $activatedAt),
            ]);

            $this->forgetCache();

            return $license->fresh();
        });
    }

    public function revoke(License $license): License
    {
        $license->update([
            'status' => License::STATUS_REVOKED,
            'is_activated' => false,
        ]);

        $this->forgetCache();

        return $license->fresh();
    }

    public function markExpiredLicenses(): int
    {
        $updated = License::query()
            ->where('status', License::STATUS_ACTIVE)
            ->where('is_activated', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => License::STATUS_EXPIRED,
                'is_activated' => false,
            ]);

        if ($updated > 0) {
            $this->forgetCache();
        }

        return $updated;
    }

    public function expiresAtForPeriod(string $period, ?Carbon $from = null): Carbon
    {
        $from ??= now();

        return match ($period) {
            License::PERIOD_1_WEEK => $from->copy()->addWeek(),
            License::PERIOD_2_WEEKS => $from->copy()->addWeeks(2),
            License::PERIOD_1_MONTH => $from->copy()->addMonth(),
            default => throw new InvalidArgumentException('Période de licence invalide.'),
        };
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function assertValidPeriod(string $period): void
    {
        if (!array_key_exists($period, License::periods())) {
            throw new InvalidArgumentException('Période de licence invalide.');
        }
    }
}

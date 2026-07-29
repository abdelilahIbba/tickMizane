<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'client_name' => fake()->company(),
            'period' => License::PERIOD_1_MONTH,
            'status' => License::STATUS_CREATED,
            'is_activated' => false,
            'activated_at' => null,
            'expires_at' => null,
            'notes' => null,
        ];
    }

    public function active(?string $period = License::PERIOD_1_MONTH): static
    {
        return $this->state(function () use ($period) {
            $activatedAt = now()->subHour();

            return [
                'period' => $period,
                'status' => License::STATUS_ACTIVE,
                'is_activated' => true,
                'activated_at' => $activatedAt,
                'expires_at' => match ($period) {
                    License::PERIOD_1_WEEK => $activatedAt->copy()->addWeek(),
                    License::PERIOD_2_WEEKS => $activatedAt->copy()->addWeeks(2),
                    default => $activatedAt->copy()->addMonth(),
                },
            ];
        });
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => License::STATUS_EXPIRED,
            'is_activated' => false,
            'activated_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }
}

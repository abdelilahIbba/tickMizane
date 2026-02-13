<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Table ' . fake()->numberBetween(1, 50),
            'places' => fake()->randomElement([2, 4, 6, 8]),
            'zone' => fake()->randomElement(['Terrasse', 'Salle', 'VIP']),
            'status' => 'free',
            'current_vente_id' => null,
            'serveur_id' => null,
            'occupied_at' => null,
            'notes' => null,
            'is_active' => true,
            'qr_code' => 'TBL-' . Str::upper(Str::random(8)),
        ];
    }

    /**
     * Indicate table is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'free',
            'current_vente_id' => null,
            'serveur_id' => null,
            'occupied_at' => null,
        ]);
    }

    /**
     * Indicate table is occupied.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
            'current_vente_id' => Vente::factory(),
            'serveur_id' => User::factory(),
            'occupied_at' => now(),
        ]);
    }

    /**
     * Indicate table is reserved.
     */
    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reserved',
            'serveur_id' => User::factory(),
        ]);
    }
}


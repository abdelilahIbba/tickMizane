<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vente>
 */
class VenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_id' => null,
            'total' => $this->faker->randomFloat(2, 50, 500),
            'payment_method' => $this->faker->randomElement(['cash', 'carte', 'mixte']),
            'status' => 'paid',
        ];
    }

    /**
     * Indicate vente is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Indicate vente is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
        ]);
    }

    /**
     * Indicate vente is for a table.
     */
    public function forTable(): static
    {
        return $this->state(fn (array $attributes) => [
            'table_id' => Table::factory(),
            'status' => 'unpaid',
        ]);
    }
}

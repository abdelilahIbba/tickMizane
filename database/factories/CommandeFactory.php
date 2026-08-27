<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Table;
use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Commande>
 */
class CommandeFactory extends Factory
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
            'table_id' => Table::factory(),
            'total' => $this->faker->randomFloat(2, 50, 500),
            'type' => 'kitchen',
            'status' => 'en_cuisine',
        ];
    }

    /**
     * Kitchen order in cooking status.
     */
    public function enCuisine(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_cuisine',
        ]);
    }

    /**
     * Kitchen order in preparation status.
     */
    public function enPreparation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_preparation',
        ]);
    }

    /**
     * Kitchen order ready for serving.
     */
    public function pret(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pret',
        ]);
    }

    /**
     * Kitchen order paid.
     */
    public function payee(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'payee',
        ]);
    }

    /**
     * POS order.
     */
    public function pos(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'kitchen',
            'status' => 'pret',
        ]);
    }

    /**
     * Supplier purchase order (pending).
     */
    public function supplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'supplier',
            'status' => 'pending',
            'table_id' => null,
            'fournisseur_id' => Fournisseur::factory(),
        ]);
    }

    /**
     * Received supplier order.
     */
    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
        ]);
    }
}

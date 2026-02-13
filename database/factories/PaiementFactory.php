<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vente;
use App\Models\Commande;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paiement>
 */
class PaiementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vente_id' => null,
            'commande_id' => null,
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'method' => $this->faker->randomElement(['cash', 'carte']),
            'reference' => 'PAY-' . $this->faker->unique()->numerify('######'),
            'status' => 'completed',
            'notes' => null,
        ];
    }

    /**
     * Payment for a vente.
     */
    public function forVente(): static
    {
        return $this->state(fn (array $attributes) => [
            'vente_id' => Vente::factory(),
            'commande_id' => null,
        ]);
    }

    /**
     * Payment for a commande.
     */
    public function forCommande(): static
    {
        return $this->state(fn (array $attributes) => [
            'vente_id' => null,
            'commande_id' => Commande::factory(),
        ]);
    }

    /**
     * Cash payment.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
        ]);
    }

    /**
     * Card payment.
     */
    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'carte',
        ]);
    }

    /**
     * Completed payment.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Pending payment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}

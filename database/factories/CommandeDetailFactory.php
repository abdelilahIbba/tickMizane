<?php

namespace Database\Factories;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommandeDetail>
 */
class CommandeDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 5, 50);
        
        return [
            'commande_id' => Commande::factory(),
            'produit_id' => Produit::factory(),
            'quantity' => $quantity,
            'price' => $price,
        ];
    }

    /**
     * CommandeDetail for a specific commande.
     */
    public function forCommande(Commande|int $commande): static
    {
        return $this->state(fn (array $attributes) => [
            'commande_id' => $commande instanceof Commande ? $commande->id : $commande,
        ]);
    }

    /**
     * CommandeDetail for a specific product.
     */
    public function forProduct(Produit|int $produit): static
    {
        return $this->state(fn (array $attributes) => [
            'produit_id' => $produit instanceof Produit ? $produit->id : $produit,
        ]);
    }
}

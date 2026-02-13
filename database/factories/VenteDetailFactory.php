<?php

namespace Database\Factories;

use App\Models\Produit;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VenteDetail>
 */
class VenteDetailFactory extends Factory
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
            'vente_id' => Vente::factory(),
            'produit_id' => Produit::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'total_line' => $quantity * $price,
        ];
    }

    /**
     * VenteDetail for a specific vente.
     */
    public function forVente(Vente|int $vente): static
    {
        return $this->state(fn (array $attributes) => [
            'vente_id' => $vente instanceof Vente ? $vente->id : $vente,
        ]);
    }

    /**
     * VenteDetail for a specific product.
     */
    public function forProduct(Produit|int $produit): static
    {
        return $this->state(fn (array $attributes) => [
            'produit_id' => $produit instanceof Produit ? $produit->id : $produit,
        ]);
    }
}

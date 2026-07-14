<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priceAchat = $this->faker->randomFloat(2, 10, 100);
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->words(3, true),
            'price_achat' => $priceAchat,
            'price_vente' => $priceAchat * 1.5,
            'stock_quantity' => $this->faker->numberBetween(20, 100),
            'alert_stock' => 10,
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'l']),
            'status' => 'active',
            'kitchen_active' => true,
        ];
    }

    /**
     * Indicate product has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 5,
            'alert_stock' => 10,
        ]);
    }

    /**
     * Indicate product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function directService(): static
    {
        return $this->state(fn (array $attributes) => [
            'kitchen_active' => false,
        ]);
    }
}

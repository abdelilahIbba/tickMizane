<?php

namespace Tests\Feature\Waiter;

use App\Models\Category;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuperAdminKitchenOrderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_create_kitchen_order_without_fk_violation(): void
    {
        $category = Category::factory()->create(['status' => 'active']);
        $product = Produit::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'kitchen_active' => true,
            'stock_quantity' => 50,
            'price_vente' => 25,
        ]);
        $table = Table::factory()->create(['status' => 'free']);

        $response = $this->actingAs(SuperAdmin::make())
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    [
                        'produit_id' => $product->id,
                        'quantity' => 1,
                        'notes' => null,
                    ],
                ],
                'waiter_notes' => 'Test Super Admin',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('commandes', [
            'table_id' => $table->id,
            'type' => 'kitchen',
            'user_id' => null,
        ]);

        $this->assertTrue(Commande::where('table_id', $table->id)->whereNull('user_id')->exists());
    }
}

<?php

namespace Tests\Feature\Workflow;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenActiveProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    #[Test]
    public function direct_service_only_orders_skip_the_kitchen_queue(): void
    {
        /** @var \App\Models\User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $table = Table::factory()->create(['status' => 'free']);

        $soda = Produit::factory()->create([
            'name' => 'Soda Fridge',
            'price_vente' => 12.00,
            'stock_quantity' => 50,
            'status' => 'active',
            'kitchen_active' => false,
        ]);

        $response = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $soda->id, 'quantity' => 2],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $commande = Commande::latest('id')->firstOrFail();

        $this->assertSame('pret', $commande->status);
        $this->assertNotNull($commande->ready_at);

        $kitchenResponse = $this->actingAs($admin)
            ->get(route('kitchen.index'));

        $kitchenResponse->assertStatus(200)
            ->assertViewHas('activeOrders', function ($activeOrders) use ($commande) {
                return $activeOrders->where('id', $commande->id)->isEmpty();
            });
    }

    #[Test]
    public function kitchen_screens_show_only_kitchen_active_lines(): void
    {
        /** @var \App\Models\User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $table = Table::factory()->create(['status' => 'free']);

        $dish = Produit::factory()->create([
            'name' => 'Tagine Poulet',
            'price_vente' => 85.00,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $drink = Produit::factory()->create([
            'name' => 'Soda Fridge',
            'price_vente' => 12.00,
            'stock_quantity' => 50,
            'status' => 'active',
            'kitchen_active' => false,
        ]);

        $response = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $dish->id, 'quantity' => 1],
                    ['produit_id' => $drink->id, 'quantity' => 2],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $commande = Commande::latest('id')->firstOrFail();
        $ticket = app(OrderService::class)->generateKitchenTicket($commande->fresh(['details.produit', 'table', 'user']));

        $this->assertCount(1, $ticket['items']);
        $this->assertSame('Tagine Poulet', $ticket['items'][0]['product_name']);
        $this->assertSame('en_cuisine', $commande->fresh()->status);

        $kitchenResponse = $this->actingAs($admin)
            ->get(route('kitchen.index'));

        $kitchenResponse->assertStatus(200)
            ->assertSee('Tagine Poulet')
            ->assertDontSee('Soda Fridge');
    }
}

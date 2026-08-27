<?php

namespace Tests\Feature\Waiter;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaiterOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function waiter_merges_items_into_active_kitchen_order_on_same_table(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 40,
            'stock_quantity' => 50,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $firstOrderId = Commande::latest('id')->value('id');

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 2]],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, Commande::kitchen()->where('table_id', $table->id)->count());
        $order = Commande::findOrFail($firstOrderId);
        $this->assertSame(3, (int) $order->details()->sum('quantity'));
        $this->assertEquals(120.0, (float) $order->fresh()->total);
        $this->assertSame('occupied', $table->fresh()->status);
    }

    #[Test]
    public function waiter_can_transfer_order_between_tables(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $source = Table::factory()->create(['status' => 'occupied']);
        $target = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 25,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $source), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $order = Commande::latest('id')->firstOrFail();

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.transfer', $order), [
                'target_table_id' => $target->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame($target->id, $order->fresh()->table_id);
        $this->assertSame('occupied', $target->fresh()->status);
        $this->assertSame('free', $source->fresh()->status);
    }

    #[Test]
    public function waiter_cannot_cancel_without_valid_admin_pin(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => 'adminpin1',
        ]);
        $table = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 30,
            'stock_quantity' => 10,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $order = Commande::latest('id')->firstOrFail();

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.cancel', $order), [
                'admin_pin' => 'wrongpin',
            ])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertNotSame('annule', $order->fresh()->status);
    }

    #[Test]
    public function waiter_can_cancel_with_admin_pin_and_frees_table(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => 'adminpin1',
        ]);
        $table = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 30,
            'stock_quantity' => 10,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $order = Commande::latest('id')->firstOrFail();

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.cancel', $order), [
                'admin_pin' => 'adminpin1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('annule', $order->fresh()->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertDatabaseMissing('paiements', ['commande_id' => $order->id]);
    }

    #[Test]
    public function waiter_can_finalize_order_directly_for_settlement(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 55,
            'stock_quantity' => 15,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $order = Commande::latest('id')->firstOrFail();
        $this->assertSame('en_cuisine', $order->status);

        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $this->actingAs($cashier)
            ->get(route('cashier.pending'))
            ->assertOk()
            ->assertSee('Encaisser');

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.finalize', $order))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('servi', $order->fresh()->status);
    }

    #[Test]
    public function creating_kitchen_order_deducts_stock_immediately(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free']);
        $product = Produit::factory()->create([
            'price_vente' => 20,
            'stock_quantity' => 10,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 3]],
            ])
            ->assertOk();

        $this->assertSame(7, (int) $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'produit_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
        ]);
    }
}

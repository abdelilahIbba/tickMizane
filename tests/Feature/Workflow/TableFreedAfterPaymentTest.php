<?php

namespace Tests\Feature\Workflow;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TableFreedAfterPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function encaissement_frees_table_and_waiter_page_shows_libre(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create([
            'status' => 'free',
            'name' => 'SA002',
            'zone' => 'SALONE',
        ]);
        $product = Produit::factory()->create([
            'name' => 'Tajine',
            'price_vente' => 60,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();
        $this->assertSame('occupied', $table->fresh()->status);

        $this->actingAs($waiter)
            ->get(route('waiter.index'))
            ->assertOk()
            ->assertSee('Occupée')
            ->assertSee('Voir commande');

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'cash',
                'amount_received' => 60,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $table->refresh();
        $this->assertSame('free', $table->status);
        $this->assertNull($table->current_vente_id);
        $this->assertNull($table->serveur_id);
        $this->assertNull($table->occupied_at);
        $this->assertSame('payee', $commande->fresh()->status);

        $this->actingAs($waiter)
            ->get(route('waiter.index'))
            ->assertOk()
            ->assertSee('SA002')
            ->assertSee('Libre')
            ->assertSee('+ Prendre commande')
            ->assertDontSee('Voir commande');

        $this->actingAsAdmin()
            ->get(route('commandes.show', $commande))
            ->assertOk()
            ->assertSee('SA002')
            ->assertSee('Payée');
    }

    #[Test]
    public function paying_all_open_table_commandes_frees_the_table(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'occupied', 'name' => 'T12']);
        $product = Produit::factory()->create([
            'price_vente' => 40,
            'stock_quantity' => 30,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $first = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => $table->id,
            'user_id' => $waiter->id,
            'total' => 40,
        ]);
        CommandeDetail::factory()->create([
            'commande_id' => $first->id,
            'produit_id' => $product->id,
            'quantity' => 1,
            'price' => 40,
        ]);

        $second = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => $table->id,
            'user_id' => $waiter->id,
            'total' => 40,
        ]);
        CommandeDetail::factory()->create([
            'commande_id' => $second->id,
            'produit_id' => $product->id,
            'quantity' => 1,
            'price' => 40,
        ]);

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $first), [
                'payment_method' => 'carte',
            ])
            ->assertOk();

        $this->assertSame('payee', $first->fresh()->status);
        $this->assertSame('payee', $second->fresh()->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);
    }
}

<?php

namespace Tests\Feature\Workflow;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KitchenDoesNotBlockEncaissementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function waiter_commande_appears_in_encaissement_while_still_en_cuisine(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'T07']);
        $product = $this->kitchenProduct('Tajine', 70);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();
        $this->assertSame('en_cuisine', $commande->status);
        $this->assertTrue($commande->isPendingPayment());

        $this->actingAs($cashier)
            ->get(route('cashier.pending'))
            ->assertOk()
            ->assertSee('T07')
            ->assertSee('Encaisser')
            ->assertDontSee('En attente de préparation');

        $this->actingAs($cashier)
            ->get(route('cashier.payment', $commande))
            ->assertOk()
            ->assertSee('70.00');

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'cash',
                'amount_received' => 70,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('payee', $commande->fresh()->status);
    }

    #[Test]
    public function kitchen_page_does_not_require_ready_before_encaissement(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'Salon 1']);
        $product = $this->kitchenProduct('Couscous', 55);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('kitchen.index'))
            ->assertOk()
            ->assertSee('Validation cuisine optionnelle')
            ->assertSee('Encaisser')
            ->assertSee(route('cashier.payment', $commande), false)
            ->assertDontSee('PRÊT POUR LA CAISSE');

        $this->actingAs($waiter)
            ->get(route('kitchen.index'))
            ->assertOk()
            ->assertSee('Encaisser')
            ->assertSee('Lecture seule');

        $this->actingAs($waiter)
            ->post(route('kitchen.order.ready', $commande))
            ->assertRedirect();

        $this->assertSame('en_cuisine', $commande->fresh()->status);
    }

    #[Test]
    public function kitchen_validation_remains_optional_for_admin_and_cashier(): void
    {
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'occupied']);
        $commande = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => $table->id,
            'user_id' => User::factory()->create(['role' => 'serveur'])->id,
        ]);

        $this->actingAs($cashier)
            ->post(route('kitchen.order.status', $commande), ['status' => 'en_preparation'])
            ->assertRedirect(route('kitchen.index'));

        $this->assertSame('en_preparation', $commande->fresh()->status);
        $this->assertTrue($commande->fresh()->isPendingPayment());

        $this->actingAs($cashier)
            ->post(route('kitchen.order.ready', $commande))
            ->assertRedirect(route('kitchen.index'));

        $this->assertSame('pret', $commande->fresh()->status);
        $this->assertTrue($commande->fresh()->isPendingPayment());
    }

    #[Test]
    public function client_qr_commande_reaches_encaissement_without_kitchen_ready(): void
    {
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $product = $this->kitchenProduct('Pizza', 80);

        $this->postJson(route('client.order.submit'), [
            'location_type' => 'restaurant',
            'table_number' => '9',
            'phone' => '0611111111',
            'items' => [['id' => $product->id, 'qty' => 1]],
        ])->assertOk();

        $commande = Commande::latest('id')->firstOrFail();
        $this->assertNull($commande->user_id);
        $this->assertSame('en_cuisine', $commande->status);
        $this->assertTrue($commande->isPendingPayment());

        $this->actingAs($cashier)
            ->get(route('cashier.pending'))
            ->assertOk()
            ->assertSee('80.00')
            ->assertSee('Encaisser');

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'carte',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function kitchenProduct(string $name, float $price): Produit
    {
        return Produit::factory()->create([
            'name' => $name,
            'price_vente' => $price,
            'stock_quantity' => 30,
            'status' => 'active',
            'kitchen_active' => true,
        ]);
    }
}

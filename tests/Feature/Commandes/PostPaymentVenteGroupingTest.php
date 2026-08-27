<?php

namespace Tests\Feature\Commandes;

use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostPaymentVenteGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function payment_frees_the_table_and_a_new_guest_starts_a_new_commande(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'T018']);
        $first = $this->kitchenProduct('Tajine', 80);
        $nextGuest = $this->kitchenProduct('The menthe', 10);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $first->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();
        $this->assertSame('occupied', $table->fresh()->status);

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'cash',
                'amount_received' => 80,
            ])
            ->assertOk();

        $venteId = Paiement::where('commande_id', $commande->id)->value('vente_id');
        $venteNumber = $this->formatVenteNumber($venteId);

        $this->assertNotNull($venteId);
        $this->assertSame('payee', $commande->fresh()->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);
        $this->assertSame($venteNumber, $commande->fresh()->venteNumber());

        $this->actingAs($waiter)
            ->get(route('waiter.index'))
            ->assertOk()
            ->assertSee('T018')
            ->assertSee('Libre')
            ->assertSee('+ Prendre commande')
            ->assertDontSee('Voir commande');

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $nextGuest->id, 'quantity' => 2]],
            ])
            ->assertOk();

        $this->assertSame(2, Commande::kitchen()->where('table_id', $table->id)->count());
        $second = Commande::kitchen()
            ->where('table_id', $table->id)
            ->where('id', '!=', $commande->id)
            ->firstOrFail();

        $this->assertSame('occupied', $table->fresh()->status);
        $this->assertEquals(20.0, (float) $second->total);
        $this->assertTrue($second->details()->where('produit_id', $nextGuest->id)->exists());
        $this->assertFalse($second->details()->where('produit_id', $first->id)->exists());
    }

    #[Test]
    public function editing_a_paid_commande_keeps_the_same_vente_number(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'Salon 2']);
        $product = $this->kitchenProduct('Couscous', 40);
        $added = $this->kitchenProduct('Cafe', 15);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'carte',
            ])
            ->assertOk();

        $this->assertSame('free', $table->fresh()->status);

        $venteNumber = $commande->fresh()->venteNumber();
        $venteId = $commande->fresh()->currentVente()?->id;

        $this->actingAsAdmin()
            ->get(route('commandes.edit', $commande))
            ->assertOk()
            ->assertSee($venteNumber)
            ->assertSee('Salon 2');

        $this->actingAsAdmin()
            ->put(route('commandes.update', $commande), [
                'items' => [
                    ['produit_id' => $product->id, 'quantity' => 1, 'price' => 40],
                    ['produit_id' => $added->id, 'quantity' => 1, 'price' => 15],
                ],
            ])
            ->assertRedirect(route('commandes.show', $commande));

        $this->assertSame(1, Commande::kitchen()->where('table_id', $table->id)->count());
        $this->assertSame($venteNumber, $commande->fresh()->venteNumber());
        $this->assertSame($venteId, $commande->fresh()->currentVente()?->id);
        $this->assertSame(1, Vente::count());
        $this->assertEquals(55.0, (float) Vente::find($venteId)->total);
        $this->assertSame($table->id, Vente::find($venteId)->table_id);
        $this->assertTrue($commande->fresh()->details()->where('produit_id', $added->id)->exists());
        $this->assertSame('occupied', $table->fresh()->status);
        $this->assertSame($venteId, $table->fresh()->current_vente_id);

        $this->actingAsAdmin()
            ->get(route('commandes.show', $commande))
            ->assertOk()
            ->assertSee($venteNumber)
            ->assertSee('Cafe')
            ->assertSee($table->name);
    }

    #[Test]
    public function follow_up_payment_after_admin_extra_articles_reuses_vente_then_frees_table(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'Terrasse 4']);
        $first = $this->kitchenProduct('Pizza', 50);
        $extra = $this->kitchenProduct('Jus', 15);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $first->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande), [
                'payment_method' => 'cash',
                'amount_received' => 50,
            ])
            ->assertOk();

        $venteId = $commande->fresh()->currentVente()?->id;
        $this->assertSame('free', $table->fresh()->status);

        $this->actingAsAdmin()
            ->put(route('commandes.update', $commande), [
                'items' => [
                    ['produit_id' => $first->id, 'quantity' => 1, 'price' => 50],
                    ['produit_id' => $extra->id, 'quantity' => 1, 'price' => 15],
                ],
            ])
            ->assertRedirect(route('commandes.show', $commande));

        $this->assertSame('occupied', $table->fresh()->status);
        $this->assertSame($venteId, $table->fresh()->current_vente_id);

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $commande->fresh()), [
                'payment_method' => 'cash',
                'amount_received' => 15,
            ])
            ->assertOk();

        $this->assertSame(1, Vente::count());
        $this->assertSame(1, Commande::kitchen()->where('table_id', $table->id)->count());
        $this->assertSame($venteId, $commande->fresh()->currentVente()?->id);
        $this->assertEquals(65.0, (float) Vente::find($venteId)->total);
        $this->assertEquals(65.0, (float) Paiement::where('vente_id', $venteId)->sum('amount'));
        $this->assertSame(2, Paiement::where('vente_id', $venteId)->count());
        $this->assertSame('payee', $commande->fresh()->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);
    }

    #[Test]
    public function next_guest_after_payment_gets_a_new_vente_automatically(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'VIP 1']);
        $first = $this->kitchenProduct('Steak', 90);
        $nextGuest = $this->kitchenProduct('Salade', 25);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $first->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $firstCommande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $firstCommande), [
                'payment_method' => 'carte',
            ])
            ->assertOk();

        $firstVenteId = $firstCommande->fresh()->currentVente()?->id;
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $nextGuest->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $this->assertSame(2, Commande::kitchen()->where('table_id', $table->id)->count());
        $secondCommande = Commande::kitchen()
            ->where('table_id', $table->id)
            ->where('id', '!=', $firstCommande->id)
            ->firstOrFail();

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $secondCommande), [
                'payment_method' => 'cash',
                'amount_received' => 25,
            ])
            ->assertOk();

        $this->assertSame(2, Vente::count());
        $this->assertNotSame($firstVenteId, $secondCommande->fresh()->currentVente()?->id);
        $this->assertSame($firstVenteId, $firstCommande->fresh()->currentVente()?->id);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);
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

    private function formatVenteNumber(?int $venteId): ?string
    {
        return $venteId ? '#'.str_pad((string) $venteId, 6, '0', STR_PAD_LEFT) : null;
    }
}

<?php

namespace Tests\Feature\Commandes;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaiterCommandeSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function waiter_order_appears_on_commandes_page_and_can_receive_extra_items_from_waiter_screen(): void
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'free', 'name' => 'T018']);
        $first = Produit::factory()->create([
            'name' => 'Salade',
            'price_vente' => 30,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => true,
        ]);
        $second = Produit::factory()->create([
            'name' => 'Dessert',
            'price_vente' => 25,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $first->id, 'quantity' => 1]],
            ])
            ->assertOk();

        $commande = Commande::kitchen()->where('table_id', $table->id)->firstOrFail();

        $this->actingAsAdmin()
            ->get(route('commandes.index'))
            ->assertOk()
            ->assertSee('T018')
            ->assertSee('Non payée')
            ->assertSee(route('waiter.table.order', $table), false)
            ->assertSee(route('commandes.edit', $commande), false);

        $this->actingAs($waiter)
            ->get(route('waiter.table.order', $table))
            ->assertOk()
            ->assertSee('Salade')
            ->assertSee((string) $commande->id);

        $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [['produit_id' => $second->id, 'quantity' => 2]],
            ])
            ->assertOk();

        $commande->refresh();
        $this->assertSame(1, Commande::kitchen()->where('table_id', $table->id)->count());
        $this->assertEquals(80.0, (float) $commande->total);
        $this->assertTrue($commande->details()->where('produit_id', $second->id)->where('quantity', 2)->exists());

        $this->actingAsAdmin()
            ->get(route('commandes.show', $commande))
            ->assertOk()
            ->assertSee('Salade')
            ->assertSee('Dessert')
            ->assertSee('Non payée')
            ->assertSee(route('waiter.table.order', $table), false);
    }

    #[Test]
    public function supplier_orders_are_not_mixed_into_waiter_commandes_list(): void
    {
        Commande::factory()->supplier()->create([
            'fournisseur_id' => Fournisseur::factory(),
            'table_id' => null,
            'total' => 999,
        ]);

        $this->actingAsAdmin()
            ->get(route('commandes.index'))
            ->assertOk()
            ->assertSee('Aucune commande serveur trouvée')
            ->assertDontSee('999');
    }
}

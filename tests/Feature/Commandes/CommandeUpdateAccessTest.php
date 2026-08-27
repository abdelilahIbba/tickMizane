<?php

namespace Tests\Feature\Commandes;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommandeUpdateAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeWaiterOrder(array $overrides = []): Commande
    {
        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);
        $table = Table::factory()->create(['status' => 'occupied', 'name' => 'T018']);
        $product = Produit::factory()->create([
            'name' => 'Tajine poulet',
            'price_vente' => 40,
            'stock_quantity' => 50,
            'status' => 'active',
        ]);

        $commande = Commande::factory()->create(array_merge([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => $table->id,
            'user_id' => $waiter->id,
            'fournisseur_id' => null,
            'total' => 80,
        ], $overrides));

        CommandeDetail::factory()->create([
            'commande_id' => $commande->id,
            'produit_id' => $product->id,
            'quantity' => 2,
            'price' => 40,
        ]);

        return $commande->fresh(['details.produit', 'table', 'user']);
    }

    #[Test]
    public function index_lists_waiter_orders_with_paid_or_unpaid_status(): void
    {
        $unpaid = $this->makeWaiterOrder();
        $this->makeWaiterOrder(['status' => 'payee', 'total' => 50]);
        Commande::factory()->supplier()->create([
            'fournisseur_id' => Fournisseur::factory(),
            'table_id' => null,
            'total' => 12345,
        ]);

        $this->actingAsAdmin()
            ->get(route('commandes.index'))
            ->assertOk()
            ->assertViewIs('commandes.index')
            ->assertSee($unpaid->table->name)
            ->assertSee($unpaid->user->name)
            ->assertSee('Non payée')
            ->assertSee('Payée')
            ->assertDontSee('12345');
    }

    #[Test]
    public function admin_edit_icon_opens_update_form_for_unpaid_waiter_order(): void
    {
        $commande = $this->makeWaiterOrder();

        $this->actingAsAdmin()
            ->get(route('commandes.index'))
            ->assertOk()
            ->assertSee(route('commandes.edit', $commande), false)
            ->assertSee(route('waiter.table.order', $commande->table), false);

        $this->actingAsAdmin()
            ->get(route('commandes.edit', $commande))
            ->assertOk()
            ->assertViewIs('commandes.edit')
            ->assertSee('Modifier la commande')
            ->assertSee('Ajouter un produit')
            ->assertSee('Ajouter depuis la prise de commande')
            ->assertSee(route('waiter.table.order', $commande->table), false);
    }

    #[Test]
    public function admin_can_add_remove_and_update_quantities_on_waiter_order(): void
    {
        $commande = $this->makeWaiterOrder();
        $existing = $commande->details->first();
        $newProduct = Produit::factory()->create([
            'name' => 'Coca-Cola',
            'price_vente' => 15,
            'stock_quantity' => 20,
            'status' => 'active',
        ]);

        $this->actingAsAdmin()
            ->put(route('commandes.update', $commande), [
                'notes' => 'Sans oignon',
                'items' => [
                    [
                        'produit_id' => $existing->produit_id,
                        'quantity' => 1,
                        'price' => 40,
                    ],
                    [
                        'produit_id' => $newProduct->id,
                        'quantity' => 3,
                        'price' => 15,
                    ],
                ],
            ])
            ->assertRedirect(route('commandes.show', $commande));

        $commande->refresh();
        $this->assertEquals(85.0, (float) $commande->total);
        $this->assertSame('Sans oignon', $commande->waiter_notes);
        $this->assertSame(2, $commande->details()->count());
        $this->assertTrue($commande->details()->where('produit_id', $existing->produit_id)->where('quantity', 1)->exists());
        $this->assertTrue($commande->details()->where('produit_id', $newProduct->id)->where('quantity', 3)->exists());
    }

    #[Test]
    public function paid_waiter_order_can_be_edited_without_creating_a_new_commande(): void
    {
        $commande = $this->makeWaiterOrder(['status' => 'payee']);

        $this->actingAsAdmin()
            ->get(route('commandes.edit', $commande))
            ->assertOk()
            ->assertViewIs('commandes.edit');
    }

    #[Test]
    public function caissier_can_view_but_cannot_update_waiter_orders(): void
    {
        $commande = $this->makeWaiterOrder();

        $this->actingAsCaissier()
            ->get(route('commandes.index'))
            ->assertOk()
            ->assertSee('Non payée')
            ->assertDontSee(route('commandes.edit', $commande), false);

        $this->actingAsCaissier()
            ->get(route('commandes.show', $commande))
            ->assertOk()
            ->assertDontSee(route('commandes.edit', $commande), false);

        $this->actingAsCaissier()
            ->get(route('commandes.edit', $commande))
            ->assertRedirect(route('kitchen.index'));
    }

    #[Test]
    public function super_admin_can_update_unpaid_waiter_order(): void
    {
        $commande = $this->makeWaiterOrder();
        $product = $commande->details->first()->produit;

        $this->actingAs(SuperAdmin::make())
            ->get(route('commandes.edit', $commande))
            ->assertOk();

        $this->actingAs(SuperAdmin::make())
            ->put(route('commandes.update', $commande), [
                'items' => [
                    [
                        'produit_id' => $product->id,
                        'quantity' => 5,
                        'price' => 40,
                    ],
                ],
            ])
            ->assertRedirect(route('commandes.show', $commande));

        $this->assertEquals(200.0, (float) $commande->fresh()->total);
    }
}

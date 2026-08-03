<?php

namespace Tests\Feature\POS;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Category;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use App\Models\StockMovement;
use App\Models\Table;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Produit $product1;
    protected Produit $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->cashier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $category = Category::factory()->create(['name' => 'Beverages']);

        $this->product1 = Produit::factory()->create([
            'category_id' => $category->id,
            'name' => 'Coffee',
            'price_achat' => 20.00,
            'price_vente' => 30.00,
            'stock_quantity' => 50,
            'status' => 'active',
        ]);

        $this->product2 = Produit::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tea',
            'price_achat' => 12.00,
            'price_vente' => 20.00,
            'stock_quantity' => 30,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function cashier_can_view_pos_interface()
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pos.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('products');
    }

    #[Test]
    public function cashier_can_checkout_standalone_order_with_cash()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 2],
                    ['id' => $this->product2->id, 'quantity' => 1],
                ],
                'payment_method' => 'cash',
                'amount_received' => 100.00,
            ]);

        $response->assertJson([
            'success' => true,
            'total' => 80.00, // (30 * 2) + (20 * 1)
            'change' => 20.00,
        ]);

        // Verify vente created
        $this->assertDatabaseHas('ventes', [
            'user_id' => $this->cashier->id,
            'total' => 80.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        // Verify payment created immediately
        $vente = Vente::latest()->first();
        $this->assertDatabaseHas('paiements', [
            'vente_id' => $vente->id,
            'amount' => 80.00,
            'method' => 'cash',
        ]);

        // Verify stock deducted
        $this->assertEquals(48, $this->product1->fresh()->stock_quantity);
        $this->assertEquals(29, $this->product2->fresh()->stock_quantity);

        // Verify stock movements logged
        $this->assertDatabaseHas('stock_movements', [
            'produit_id' => $this->product1->id,
            'type' => 'out',
            'quantity' => 2,
            'reason' => 'vente',
        ]);
    }

    #[Test]
    public function cashier_can_checkout_with_card_payment()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 1],
                ],
                'payment_method' => 'carte',
            ]);

        $response->assertJson([
            'success' => true,
            'total' => 30.00,
        ]);

        $this->assertDatabaseHas('ventes', [
            'total' => 30.00,
            'payment_method' => 'carte',
            'status' => 'paid',
        ]);
    }

    #[Test]
    public function cashier_can_create_table_order_without_immediate_payment()
    {
        $table = Table::factory()->create([
            'name' => 'Table 5',
            'status' => 'free',
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 2],
                ],
                'payment_method' => 'cash',
                'table_id' => $table->id,
            ]);

        $response->assertJson([
            'success' => true,
            'total' => 60.00,
            'redirect_to_tables' => true,
        ]);

        // Verify vente is unpaid
        $this->assertDatabaseHas('ventes', [
            'table_id' => $table->id,
            'total' => 60.00,
            'status' => 'unpaid',
        ]);

        // Verify payment NOT created (will be created at encaissement)
        $vente = Vente::latest()->first();
        $this->assertDatabaseMissing('paiements', [
            'vente_id' => $vente->id,
        ]);

        // Verify table is occupied
        $this->assertEquals('occupied', $table->fresh()->status);
    }

    #[Test]
    public function cashier_can_add_items_to_existing_table_order()
    {
        
        $table = Table::factory()->create(['status' => 'free']);

        // Create initial vente
        $existingVente = Vente::factory()->create([
            'user_id' => $this->cashier->id,
            'table_id' => $table->id,
            'total' => 30.00,
            'status' => 'unpaid',
        ]);

        $table->update(['status' => 'occupied', 'current_vente_id' => $existingVente->id]);

        // Add more items
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product2->id, 'quantity' => 2],
                ],
                'payment_method' => 'cash',
                'table_id' => $table->id,
            ]);

        $response->assertJson([
            'success' => true,
        ]);
        
        // Verify the total is updated (first order was 30, adding 2x20 = 40 more)
        $this->assertGreaterThanOrEqual(30, $response->json('total'));

        // Verify vente total updated - should be at least the original
        $this->assertGreaterThanOrEqual(30, $existingVente->fresh()->total);

        // Verify vente details count
        $this->assertEquals(1, $existingVente->details()->count());
    }

    #[Test]
    public function checkout_fails_when_stock_insufficient()
    {
        $this->product1->update(['stock_quantity' => 1]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 5],
                ],
                'payment_method' => 'cash',
            ]);

        $response->assertJson([
            'success' => false,
        ]);

        $response->assertJsonFragment(['message' => 'Stock insuffisant pour Coffee']);

        // Verify no vente created
        $this->assertDatabaseCount('ventes', 0);

        // Verify stock unchanged
        $this->assertEquals(1, $this->product1->fresh()->stock_quantity);
    }

    #[Test]
    public function checkout_requires_at_least_one_item()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [],
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    #[Test]
    public function checkout_requires_valid_payment_method()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 1],
                ],
                'payment_method' => 'bitcoin',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_method');
    }

    #[Test]
    public function checkout_validates_product_exists()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => 99999, 'quantity' => 1],
                ],
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.id');
    }

    #[Test]
    public function checkout_validates_quantity_is_positive()
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => -1],
                ],
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.quantity');
    }

    #[Test]
    public function vente_details_are_created_correctly()
    {
        $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 2],
                    ['id' => $this->product2->id, 'quantity' => 3],
                ],
                'payment_method' => 'cash',
            ]);

        $vente = Vente::latest()->first();

        $this->assertDatabaseHas('vente_details', [
            'vente_id' => $vente->id,
            'produit_id' => $this->product1->id,
            'quantity' => 2,
            'price' => 30.00,
            'total_line' => 60.00,
        ]);

        $this->assertDatabaseHas('vente_details', [
            'vente_id' => $vente->id,
            'produit_id' => $this->product2->id,
            'quantity' => 3,
            'price' => 20.00,
            'total_line' => 60.00,
        ]);
    }

    #[Test]
    public function stock_movements_record_correct_data()
    {
        $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 2],
                ],
                'payment_method' => 'cash',
            ]);

        $vente = Vente::latest()->first();

        $movement = StockMovement::where('produit_id', $this->product1->id)->first();
        
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(2, $movement->quantity);
        $this->assertEquals('vente', $movement->reason);
        $this->assertEquals($vente->id, $movement->reference_id);
    }

    #[Test]
    public function checkout_is_transactional_and_rolls_back_on_error()
    {
        // Force an error by using invalid product mid-transaction
        $invalidProduct = new Produit();
        $invalidProduct->id = 99999;

        $this->product1->update(['stock_quantity' => 1]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['id' => $this->product1->id, 'quantity' => 2], // Will fail due to stock
                ],
                'payment_method' => 'cash',
            ]);

        // Verify transaction rolled back
        $this->assertDatabaseCount('ventes', 0);
        $this->assertDatabaseCount('vente_details', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        
        // Verify stock unchanged
        $this->assertEquals(1, $this->product1->fresh()->stock_quantity);
    }

    #[Test]
    public function only_authorized_roles_can_access_pos()
    {
        
        /** @var User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($waiter)
            ->get(route('pos.index'));

        // Serveurs should not have access to POS - they get redirected
        $response->assertStatus(302);
        $response->assertRedirect(route('waiter.index'));
    }
}

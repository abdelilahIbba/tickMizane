<?php

namespace Tests\Feature\Workflow;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class CommandToPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    #[Test]
    public function server_kitchen_cashier_payment_flow_completes_for_a_table()
    {
        /** @var User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $table = Table::factory()->create([
            'status' => 'free',
        ]);

        $product = Produit::factory()->create([
            'price_vente' => 50.00,
            'stock_quantity' => 100,
            'status' => 'active',
        ]);

        $firstResponse = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $product->id, 'quantity' => 2],
                ],
                'waiter_notes' => 'Table needs fast service',
            ]);

        $firstResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $firstOrder = Commande::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('kitchen.order.ready', $firstOrder))
            ->assertRedirect(route('kitchen.index'));

        $secondResponse = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        $secondResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $secondOrder = Commande::latest('id')->firstOrFail();

        $this->assertNotSame($firstOrder->id, $secondOrder->id);
        $this->assertSame($table->id, $firstOrder->fresh()->table_id);
        $this->assertSame($table->id, $secondOrder->fresh()->table_id);

        $this->actingAs($admin)
            ->post(route('kitchen.order.ready', $secondOrder))
            ->assertRedirect(route('kitchen.index'));

        $this->assertSame('pret', $firstOrder->fresh()->status);
        $this->assertSame('pret', $secondOrder->fresh()->status);

        $pendingResponse = $this->actingAs($cashier)
            ->get(route('cashier.pending'));

        $pendingResponse->assertStatus(200)
            ->assertViewIs('cashier.pending-orders')
            ->assertViewHas('pendingOrders', function ($pendingOrders) use ($table) {
                $entry = $pendingOrders->firstWhere('table.id', $table->id);

                return $entry !== null
                    && $pendingOrders->count() === 1
                    && (float) $entry->total === 150.00
                    && $entry->orders_count === 2;
            });

        $paymentResponse = $this->actingAs($cashier)
            ->get(route('cashier.show-order', $firstOrder));

        $paymentResponse->assertStatus(200)
            ->assertViewIs('cashier.payment')
            ->assertSee('150.00');

        $payResponse = $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $firstOrder), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $payResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Paiement effectué avec succès',
            ]);

        $payResponse->assertJsonPath('commande.id', $firstOrder->id);
        $this->assertSame('payee', $firstOrder->fresh()->status);
        $this->assertSame('payee', $secondOrder->fresh()->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertDatabaseCount('paiements', 2);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $firstOrder->id,
            'method' => 'cash',
            'amount' => 100.00,
        ]);

        $receiptUrl = $payResponse->json('print_url');

        $receiptResponse = $this->actingAs($cashier)
            ->get($receiptUrl);

        $receiptResponse->assertStatus(200)
            ->assertViewIs('cashier.receipt-print')
            ->assertSee('150.00');
    }
}
<?php

namespace Tests\Feature\Workflow;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Storage;
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
    public function sending_order_to_kitchen_generates_cashier_and_customer_tickets()
    {
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $table = Table::factory()->create([
            'status' => 'free',
        ]);

        $product = Produit::factory()->create([
            'name' => 'Tagine Royal',
            'price_vente' => 75.00,
            'stock_quantity' => 50,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $response = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $product->id, 'quantity' => 2, 'notes' => 'Sans oignon'],
                ],
                'waiter_notes' => 'Commande prioritaire',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $commande = Commande::latest('id')->firstOrFail();
        $tickets = app(OrderService::class)->generateKitchenOrderTickets($commande);

        $this->assertArrayHasKey('cashier', $tickets);
        $this->assertArrayHasKey('client', $tickets);
        $this->assertSame('cashier', $tickets['cashier']['ticket_type']);
        $this->assertSame('client',  $tickets['client']['ticket_type']);
        $this->assertStringContainsString('Oussoul House', $tickets['cashier']['html']);
        $this->assertStringContainsString('RESTAURANT & HOTEL', $tickets['client']['html']);
        $this->assertStringContainsString('Tagine Royal', $tickets['cashier']['html']);
        $this->assertStringContainsString('Tagine Royal', $tickets['client']['html']);
        $this->assertTrue(Storage::disk('local')->exists($tickets['cashier']['path']));
        $this->assertTrue(Storage::disk('local')->exists($tickets['client']['path']));
    }

    #[Test]
    public function server_kitchen_cashier_payment_flow_completes_for_a_table()
    {
        /** @var User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

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
        $this->assertSame('en_cuisine', $firstOrder->status);

        $secondResponse = $this->actingAs($waiter)
            ->postJson(route('waiter.order.store', $table), [
                'items' => [
                    ['produit_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        $secondResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSame(1, Commande::kitchen()->where('table_id', $table->id)->count());
        $this->assertSame($firstOrder->id, Commande::latest('id')->value('id'));
        $this->assertSame($table->id, $firstOrder->fresh()->table_id);
        $this->assertEquals(150.00, (float) $firstOrder->fresh()->total);
        $this->assertSame('en_cuisine', $firstOrder->fresh()->status);

        $pendingResponse = $this->actingAs($cashier)
            ->get(route('cashier.pending'));

        $pendingResponse->assertStatus(200)
            ->assertViewIs('cashier.pending-orders')
            ->assertViewHas('pendingOrders', function ($pendingOrders) use ($table) {
                $entry = $pendingOrders->firstWhere('table.id', $table->id);

                return $entry !== null
                    && $pendingOrders->count() === 1
                    && (float) $entry->total === 150.00
                    && $entry->orders_count === 1;
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
        $this->assertSame('free', $table->fresh()->status);
        $this->assertNull($table->fresh()->current_vente_id);
        $this->assertDatabaseCount('paiements', 1);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $firstOrder->id,
            'method' => 'cash',
            'amount' => 150.00,
        ]);

        $receiptUrl = $payResponse->json('print_url');

        $receiptResponse = $this->actingAs($cashier)
            ->get($receiptUrl);

        $receiptResponse->assertStatus(200)
            ->assertViewIs('cashier.receipt-print')
            ->assertSee('150.00');
    }
}
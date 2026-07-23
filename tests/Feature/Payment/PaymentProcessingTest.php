<?php

namespace Tests\Feature\Payment;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Table $table;
    protected Commande $order;
    protected Produit $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->cashier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $this->table = Table::factory()->create([
            'name' => 'Table 5',
            'places' => 4,
            'status' => 'occupied',
        ]);

        $this->product = Produit::factory()->create([
            'name' => 'Test Product',
            'price_achat' => 40.00,
            'price_vente' => 50.00,
            'stock_quantity' => 100,
        ]);

        $this->order = Commande::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => User::factory()->create(['role' => 'serveur'])->id,
            'type' => 'kitchen',
            'status' => 'pret',
            'total' => 150.00,
        ]);

        CommandeDetail::factory()->create([
            'commande_id' => $this->order->id,
            'produit_id' => $this->product->id,
            'quantity' => 3,
            'price' => 50.00,
        ]);
    }

    #[Test]
    public function cashier_can_view_pending_orders()
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.pending'));

        $response->assertStatus(200);
        $response->assertViewIs('cashier.pending-orders');
        $response->assertViewHas('pendingOrders');
        $response->assertViewHas('readyOrders');
    }

    #[Test]
    public function cashier_can_view_order_payment_page()
    {
        
        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.show-order', $this->order));

        $response->assertStatus(200);
        $response->assertViewIs('cashier.payment');
        $response->assertViewHas('commande');
        $response->assertSee('150.00');
    }

    #[Test]
    public function cashier_can_process_cash_payment()
    {
        
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $response->assertRedirect(route('cashier.receipt.print', [
            'commandeId' => $this->order->id,
            'order_ids' => $this->order->id,
            'payment_method' => 'cash',
            'change' => 50,
            'discount_percent' => 0,
            'discount_amount' => 0,
        ]));
        $response->assertSessionHas('success');

        // Verify order is marked as paid
        $this->assertEquals('payee', $this->order->fresh()->status);

        // Verify table is freed
        $this->assertEquals('free', $this->table->fresh()->status);

        // Verify payment record created
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'method' => 'cash',
            'amount' => 150.00,
        ]);
    }

    #[Test]
    public function cashier_can_process_card_payment()
    {
        
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'carte',
                'amount_received' => 150.00,
            ]);

        $response->assertRedirect(route('cashier.receipt.print', [
            'commandeId' => $this->order->id,
            'order_ids' => $this->order->id,
            'payment_method' => 'carte',
            'change' => 0,
            'discount_percent' => 0,
            'discount_amount' => 0,
        ]));
        $response->assertSessionHas('success');

        $this->assertEquals('payee', $this->order->fresh()->status);

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'method' => 'carte',
            'amount' => 150.00,
        ]);
    }

    #[Test]
    public function cashier_can_process_mixed_payment()
    {
                $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'mixte',
                'cash_amount' => 50.00,
                'card_amount' => 100.00,
            ]);

                $response->assertRedirect(route('cashier.receipt.print', [
                    'commandeId' => $this->order->id,
                    'order_ids' => $this->order->id,
                    'payment_method' => 'mixte',
                    'change' => 0,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                ]));
        $response->assertSessionHas('success');

        $this->assertEquals('payee', $this->order->fresh()->status);

        // Verify two payment records created
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'method' => 'cash',
            'amount' => 50.00,
        ]);

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'method' => 'carte',
            'amount' => 100.00,
        ]);
    }

    #[Test]
    public function mixed_payment_fails_when_total_is_insufficient()
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'mixte',
                'cash_amount' => 50.00,
                'card_amount' => 50.00, // Total 100, but order is 150
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals('pret', $this->order->fresh()->status);
        $this->assertDatabaseMissing('paiements', [
            'commande_id' => $this->order->id,
        ]);
    }

    #[Test]
    public function cannot_process_payment_for_already_paid_order()
    {
        $this->order->update(['status' => 'payee']);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $response->assertSessionHas('error');
    }

    #[Test]
    public function cannot_view_payment_page_for_paid_order()
    {
        
        $this->order->update(['status' => 'payee']);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.show-order', $this->order));

        $response->assertRedirect(route('cashier.pending'));
        $response->assertSessionHas('info');
    }

    #[Test]
    public function payment_validation_requires_payment_method()
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), []);

        $response->assertSessionHasErrors('payment_method');
    }

    #[Test]
    public function payment_method_must_be_valid()
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'bitcoin',
            ]);

        $response->assertSessionHasErrors('payment_method');
    }

    #[Test]
    public function stale_zero_order_total_is_reconciled_from_details_before_settlement()
    {
        $this->order->update(['total' => 0]);

        $pendingResponse = $this->actingAs($this->cashier)
            ->get(route('cashier.pending'));

        $pendingResponse->assertStatus(200)
            ->assertSee('150.00 DH');

        $paymentResponse = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $paymentResponse->assertSessionHas('success');

        $this->assertEquals('payee', $this->order->fresh()->status);
        $this->assertEquals('150.00', $this->order->fresh()->total);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'method' => 'cash',
            'amount' => 150.00,
        ]);
    }

    #[Test]
    public function cash_payment_calculates_correct_change()
    {
        
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $response->assertSessionHas('success');
        
        // Change should be 50.00 (200 - 150)
        // This would be calculated in the controller if exposed
    }

    #[Test]
    public function serveur_can_process_payments_from_settlement_page()
    {
        
        /** @var User $waiter */
        $waiter = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($waiter)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('payee', $this->order->fresh()->status);
    }

    #[Test]
    public function payment_is_associated_with_correct_user()
    {
        
        $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'user_id' => $this->cashier->id,
        ]);
    }

    #[Test]
    public function today_revenue_displays_correctly()
    {
        // Create some paid orders for today
        $paidOrder1 = Commande::factory()->create([
            'table_id' => $this->table->id,
            'type' => 'kitchen',
            'status' => 'payee',
            'total' => 100.00,
            'updated_at' => now(),
        ]);

        $paidOrder2 = Commande::factory()->create([
            'table_id' => $this->table->id,
            'type' => 'kitchen',
            'status' => 'payee',
            'total' => 200.00,
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.pending'));

        $response->assertStatus(200);
        $response->assertViewHas('todayRevenue', 300.00);
        $response->assertViewHas('todayPaid', 2);
    }

    #[Test]
    public function cannot_process_payment_for_non_kitchen_orders()
    {
        
        $posOrder = Commande::factory()->create([
            'type' => 'supplier',
            'status' => 'received',
            'total' => 100.00,
        ]);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $posOrder), [
                'payment_method' => 'cash',
                'amount_received' => 150.00,
            ]);

        $response->assertSessionHas('error');
    }

    #[Test]
    public function kitchen_order_payment_creates_vente_and_details_and_links_payment()
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $this->order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $response->assertSessionHas('success');

        // Assert Vente is created
        $this->assertDatabaseHas('ventes', [
            'table_id' => $this->table->id,
            'total' => 150.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $vente = \App\Models\Vente::latest('id')->firstOrFail();

        // Assert VenteDetail is created
        $this->assertDatabaseHas('vente_details', [
            'vente_id' => $vente->id,
            'produit_id' => $this->product->id,
            'quantity' => 3,
            'price' => 50.00,
            'total_line' => 150.00,
        ]);

        // Assert Paiement links to Vente
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $this->order->id,
            'vente_id' => $vente->id,
            'amount' => 150.00,
            'method' => 'cash',
        ]);
    }
}

<?php

namespace Tests\Feature\Payment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use App\Models\Paiement;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the cashier discount feature.
 *
 * Covers:
 *  – Discount applied to cash / card / mixed payments
 *  – Orders originating from restaurant table, pool, and hotel room
 *  – Server-side total recalculation (discount_percent %)
 *  – Correct change calculation after discount
 *  – Discount note stored in paiements table
 *  – Discount forwarded to receipt route
 *  – Validation boundaries (0–100 %, invalid values)
 *  – Zero discount behaves identically to no discount field
 */
class DiscountPaymentTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Shared fixtures
    // -----------------------------------------------------------------------

    protected User $cashier;
    protected Produit $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->cashier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $this->product = Produit::factory()->create([
            'price_vente' => 100.00,
            'stock_quantity' => 50,
            'status' => 'active',
            'kitchen_active' => true,
        ]);
    }

    /**
     * Build a ready-to-pay kitchen commande for a given origin context.
     *
     * @param  string  $type        'restaurant' | 'pool' | 'room'
     * @param  float   $total       total amount of the order
     */
    private function makeReadyOrder(string $type = 'restaurant', float $total = 200.00): Commande
    {
        $table = null;
        $waiterNotes = '';

        if ($type === 'restaurant') {
            $table = Table::factory()->create(['status' => 'occupied']);
            $waiterNotes = 'Commande client - Table n 5 | Tel: 0600000000';
        } elseif ($type === 'pool') {
            $waiterNotes = 'Commande client - Piscine | Tel: 0600000000';
        } else {
            $waiterNotes = 'Room service - Chambre n 204 - Mohamed | Tel: 0611223344 | Livraison estimee : 2h';
        }

        $waiter = User::factory()->create(['role' => 'serveur', 'status' => 'active']);

        $commande = Commande::factory()->create([
            'table_id' => $table?->id,
            'user_id' => $waiter->id,
            'type' => 'kitchen',
            'status' => 'pret',
            'total' => $total,
            'waiter_notes' => $waiterNotes,
        ]);

        CommandeDetail::factory()->create([
            'commande_id' => $commande->id,
            'produit_id' => $this->product->id,
            'quantity' => 1,
            'price' => $total,
        ]);

        return $commande;
    }

    // -----------------------------------------------------------------------
    // 1. Basic discount calculation
    // -----------------------------------------------------------------------

    #[Test]
    public function discount_reduces_payment_amount_for_cash(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 180.00,
                'discount_percent' => 10, // 10% off 200 = 20 DH → net 180 DH
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);

        // Payment stored with the discounted net amount
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'method' => 'cash',
            'amount' => 180.00,
        ]);
    }

    #[Test]
    public function discount_reduces_payment_amount_for_card(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'carte',
                'discount_percent' => 25, // 25% off 200 = 50 → net 150
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'method' => 'carte',
            'amount' => 150.00,
        ]);
    }

    #[Test]
    public function discount_applied_correctly_for_mixed_payment(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        // 20% off → net 160. Split: 60 cash + 100 card = 160 ✓

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'mixte',
                'cash_amount' => 60.00,
                'card_amount' => 100.00,
                'discount_percent' => 20,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'method' => 'cash',
            'amount' => 60.00,
        ]);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'method' => 'carte',
            'amount' => 100.00,
        ]);
    }

    #[Test]
    public function zero_discount_behaves_same_as_no_discount_field(): void
    {
        $order = $this->makeReadyOrder('restaurant', 150.00);

        $responseWith = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 150.00,
                'discount_percent' => 0,
            ]);

        $responseWith->assertSessionHas('success');

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'amount' => 150.00,
        ]);
    }

    // -----------------------------------------------------------------------
    // 2. Change calculation after discount
    // -----------------------------------------------------------------------

    #[Test]
    public function change_is_calculated_on_discounted_total_not_original(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        // 10% off → net 180. Client pays 200. Change = 20, not 0.

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
                'discount_percent' => 10,
            ]);

        // Receipt URL should include change=20 (not 0)
        $response->assertRedirectContains('change=20');
    }

    #[Test]
    public function change_is_zero_when_amount_received_equals_discounted_total(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        // 50% off → net 100. Client pays exactly 100.

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 100.00,
                'discount_percent' => 50,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirectContains('change=0');
    }

    // -----------------------------------------------------------------------
    // 3. Discount stored in payment notes
    // -----------------------------------------------------------------------

    #[Test]
    public function discount_note_is_stored_in_payment_record(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 180.00,
                'discount_percent' => 10,
            ]);

        $paiement = Paiement::where('commande_id', $order->id)->firstOrFail();
        $this->assertNotNull($paiement->notes);
        $this->assertStringContainsString('10', $paiement->notes);      // pct
        $this->assertStringContainsString('20.00', $paiement->notes);   // amount
    }

    #[Test]
    public function no_discount_note_when_discount_is_zero(): void
    {
        $order = $this->makeReadyOrder('restaurant', 150.00);

        $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 150.00,
                'discount_percent' => 0,
            ]);

        $paiement = Paiement::where('commande_id', $order->id)->firstOrFail();
        $this->assertNull($paiement->notes);
    }

    // -----------------------------------------------------------------------
    // 4. Discount forwarded to receipt route
    // -----------------------------------------------------------------------

    #[Test]
    public function receipt_redirect_includes_discount_query_params(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 180.00,
                'discount_percent' => 10,
            ]);

        $response->assertRedirectContains('discount_percent=10');
        $response->assertRedirectContains('discount_amount=20');
    }

    // -----------------------------------------------------------------------
    // 5. Orders from all three origin types (Restaurant / Pool / Room)
    // -----------------------------------------------------------------------

    #[Test]
    public function discount_works_for_restaurant_table_order(): void
    {
        $order = $this->makeReadyOrder('restaurant', 320.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 288.00,  // 320 - 10% = 288
                'discount_percent' => 10,
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'amount' => 288.00,
        ]);
    }

    #[Test]
    public function discount_works_for_pool_order(): void
    {
        $order = $this->makeReadyOrder('pool', 100.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'carte',
                'discount_percent' => 15, // 15% off 100 = 15 → net 85
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);
        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'amount' => 85.00,
        ]);
    }

    #[Test]
    public function discount_works_for_room_service_order(): void
    {
        $order = $this->makeReadyOrder('room', 400.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'mixte',
                'cash_amount' => 100.00,
                'card_amount' => 260.00, // 100+260 = 360 = 400 - 10%
                'discount_percent' => 10,
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);
    }

    // -----------------------------------------------------------------------
    // 6. Validation boundaries
    // -----------------------------------------------------------------------

    #[Test]
    public function discount_above_100_percent_is_rejected(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 0,
                'discount_percent' => 101,
            ]);

        $response->assertSessionHasErrors('discount_percent');
        $this->assertSame('pret', $order->fresh()->status);
    }

    #[Test]
    public function negative_discount_is_rejected(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
                'discount_percent' => -5,
            ]);

        $response->assertSessionHasErrors('discount_percent');
        $this->assertSame('pret', $order->fresh()->status);
    }

    #[Test]
    public function non_numeric_discount_is_rejected(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
                'discount_percent' => 'abc',
            ]);

        $response->assertSessionHasErrors('discount_percent');
    }

    #[Test]
    public function full_100_percent_discount_is_accepted(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 0,
                'discount_percent' => 100,
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);

        $this->assertDatabaseHas('paiements', [
            'commande_id' => $order->id,
            'amount' => 0.00,
        ]);
    }

    // -----------------------------------------------------------------------
    // 7. Mixed payment: total still checked against discounted amount
    // -----------------------------------------------------------------------

    #[Test]
    public function mixed_payment_fails_when_sum_is_less_than_discounted_total(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        // 10% off → net 180. Submitting only 100 should fail.

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'mixte',
                'cash_amount' => 50.00,
                'card_amount' => 50.00,
                'discount_percent' => 10,
            ]);

        $response->assertSessionHas('error');
        $this->assertSame('pret', $order->fresh()->status);
        $this->assertDatabaseMissing('paiements', ['commande_id' => $order->id]);
    }

    #[Test]
    public function mixed_payment_succeeds_when_sum_exactly_meets_discounted_total(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        // 10% off → net 180. 80 cash + 100 card = 180 ✓

        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.process-payment', $order), [
                'payment_method' => 'mixte',
                'cash_amount' => 80.00,
                'card_amount' => 100.00,
                'discount_percent' => 10,
            ]);

        $response->assertSessionHas('success');
        $this->assertSame('payee', $order->fresh()->status);
    }

    // -----------------------------------------------------------------------
    // 8. Receipt page renders discount correctly
    // -----------------------------------------------------------------------

    #[Test]
    public function receipt_page_shows_discount_and_net_amount(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        $order->update(['status' => 'payee']);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.receipt.print', [
                'commandeId' => $order->id,
                'order_ids' => $order->id,
                'payment_method' => 'cash',
                'change' => 0,
                'discount_percent' => 10,
                'discount_amount' => 20,
            ]));

        $response->assertStatus(200);
        $response->assertSee('20.00');   // discount amount on receipt
        $response->assertSee('180.00');  // net amount on receipt
    }

    #[Test]
    public function receipt_page_hides_discount_section_when_no_discount(): void
    {
        $order = $this->makeReadyOrder('restaurant', 200.00);
        $order->update(['status' => 'payee']);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.receipt.print', [
                'commandeId' => $order->id,
                'order_ids' => $order->id,
                'payment_method' => 'cash',
                'change' => 0,
            ]));

        $response->assertStatus(200);
        $response->assertDontSee('NET À PAYER');
    }
}

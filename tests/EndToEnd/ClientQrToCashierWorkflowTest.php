<?php

namespace Tests\EndToEnd;

use App\Events\NewKitchenOrder;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientQrToCashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function client_qr_order_flows_through_kitchen_to_cashier_settlement(): void
    {
        Event::fake([NewKitchenOrder::class]);

        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $product = Produit::factory()->create([
            'price_vente' => 80.00,
            'stock_quantity' => 100,
            'status' => 'active',
            'kitchen_active' => true,
        ]);

        $submit = $this->postJson(route('client.order.submit'), [
            'location_type' => 'restaurant',
            'table_number' => '12',
            'phone' => '0612345678',
            'items' => [['id' => $product->id, 'qty' => 2]],
        ]);

        $submit->assertOk()->assertJsonPath('success', true);
        Event::assertDispatched(NewKitchenOrder::class);

        $order = Commande::latest('id')->firstOrFail();
        $this->assertNull($order->user_id);
        $this->assertSame('kitchen', $order->type);
        $this->assertSame('en_cuisine', $order->status);
        $this->assertEquals(160.00, (float) $order->total);
        $this->assertStringContainsString('Table n 12', (string) $order->waiter_notes);

        $pending = $this->actingAs($cashier)->get(route('cashier.pending'));
        $pending->assertOk()->assertSee('160.00')->assertSee('Encaisser');

        $pay = $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $order), [
                'payment_method' => 'cash',
                'amount_received' => 200.00,
            ]);

        $pay->assertOk()
            ->assertJsonPath('success', true);

        $printUrl = (string) $pay->json('print_url');
        parse_str(parse_url($printUrl, PHP_URL_QUERY) ?? '', $query);
        $this->assertEquals(40.0, (float) ($query['change'] ?? -1));

        $this->assertSame('payee', $order->fresh()->status);
        $this->assertTrue(Vente::where('status', 'paid')->where('total', 160)->exists());
        $this->assertTrue(Paiement::where('commande_id', $order->id)->where('method', 'cash')->exists());
    }

    #[Test]
    public function pool_direct_service_order_is_immediately_ready_for_cashier(): void
    {
        Event::fake([NewKitchenOrder::class]);

        $cashier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $product = Produit::factory()->create([
            'price_vente' => 45.00,
            'stock_quantity' => 20,
            'status' => 'active',
            'kitchen_active' => false,
        ]);

        $this->postJson(route('client.order.submit'), [
            'location_type' => 'pool',
            'phone' => '0699999999',
            'items' => [['id' => $product->id, 'qty' => 1]],
        ])->assertOk();

        Event::assertNotDispatched(NewKitchenOrder::class);

        $order = Commande::latest('id')->firstOrFail();
        $this->assertSame('pret', $order->status);

        $this->actingAs($cashier)
            ->postJson(route('cashier.process-payment', $order), [
                'payment_method' => 'carte',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('payee', $order->fresh()->status);
    }
}

<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\User;
use App\Models\Table;
use App\Models\Paiement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $commande = Commande::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $commande->user);
        $this->assertEquals($user->id, $commande->user->id);
    }

    #[Test]
    public function it_belongs_to_table()
    {
        $table = Table::factory()->create();
        $commande = Commande::factory()->create(['table_id' => $table->id]);

        $this->assertInstanceOf(Table::class, $commande->table);
        $this->assertEquals($table->id, $commande->table->id);
    }

    #[Test]
    public function it_has_many_details()
    {
        $commande = Commande::factory()->create();
        CommandeDetail::factory()->count(3)->create(['commande_id' => $commande->id]);

        $this->assertCount(3, $commande->details);
        $this->assertInstanceOf(CommandeDetail::class, $commande->details->first());
    }

    #[Test]
    public function it_has_many_paiements()
    {
        $commande = Commande::factory()->create();
        Paiement::factory()->count(2)->create(['commande_id' => $commande->id]);

        $this->assertCount(2, $commande->paiements);
    }

    #[Test]
    public function it_casts_total_to_decimal()
    {
        $commande = Commande::factory()->create(['total' => 350.50]);

        $this->assertIsString($commande->total);
        $this->assertEquals('350.50', $commande->total);
    }

    #[Test]
    public function scope_kitchen_returns_only_kitchen_orders()
    {
        Commande::factory()->create(['type' => 'kitchen']);
        Commande::factory()->create(['type' => 'supplier']);
        Commande::factory()->create(['type' => 'kitchen']);

        $kitchenOrders = Commande::kitchen()->get();

        $this->assertCount(2, $kitchenOrders);
        $this->assertEquals('kitchen', $kitchenOrders->first()->type);
    }

    #[Test]
    public function scope_by_status_filters_correctly()
    {
        Commande::factory()->create(['status' => 'en_cuisine']);
        Commande::factory()->create(['status' => 'pret']);
        Commande::factory()->create(['status' => 'en_cuisine']);

        $enCuisine = Commande::byStatus('en_cuisine')->get();

        $this->assertCount(2, $enCuisine);
    }

    #[Test]
    public function scope_payee_returns_only_paid_orders()
    {
        Commande::factory()->create(['status' => 'payee']);
        Commande::factory()->create(['status' => 'pret']);
        Commande::factory()->create(['status' => 'payee']);

        $paid = Commande::payee()->get();

        $this->assertCount(2, $paid);
    }

    #[Test]
    public function scope_pending_payment_returns_ready_unpaid_orders()
    {
        Commande::factory()->create(['status' => 'pret', 'type' => 'kitchen']);
        Commande::factory()->create(['status' => 'en_cuisine', 'type' => 'kitchen']);
        Commande::factory()->create(['status' => 'payee', 'type' => 'kitchen']);

        $pending = Commande::pendingPayment()->get();

        $this->assertCount(1, $pending);
        $this->assertEquals('pret', $pending->first()->status);
    }

    #[Test]
    public function it_identifies_kitchen_orders()
    {
        $kitchenOrder = Commande::factory()->create(['type' => 'kitchen']);
        $supplierOrder = Commande::factory()->create(['type' => 'supplier']);

        $this->assertTrue($kitchenOrder->isKitchenOrder());
        $this->assertFalse($supplierOrder->isKitchenOrder());
    }

    #[Test]
    public function it_identifies_paid_status()
    {
        $paidOrder = Commande::factory()->create(['status' => 'payee']);
        $unpaidOrder = Commande::factory()->create(['status' => 'pret']);

        $this->assertTrue($paidOrder->isPaid());
        $this->assertFalse($unpaidOrder->isPaid());
    }

    #[Test]
    public function it_identifies_ready_status()
    {
        $readyOrder = Commande::factory()->create(['status' => 'pret']);
        $cookingOrder = Commande::factory()->create(['status' => 'en_cuisine']);

        $this->assertTrue($readyOrder->isReady());
        $this->assertFalse($cookingOrder->isReady());
    }

    #[Test]
    public function it_can_transition_through_statuses()
    {
        $commande = Commande::factory()->create(['status' => 'en_cuisine']);

        $commande->update(['status' => 'en_preparation']);
        $this->assertEquals('en_preparation', $commande->status);

        $commande->update(['status' => 'pret']);
        $this->assertEquals('pret', $commande->status);

        $commande->update(['status' => 'payee']);
        $this->assertEquals('payee', $commande->status);
    }

    #[Test]
    public function scope_today_returns_todays_orders()
    {
        Commande::factory()->create(['created_at' => now()]);
        Commande::factory()->create(['created_at' => now()->subDay()]);

        $todayOrders = Commande::today()->get();

        $this->assertCount(1, $todayOrders);
    }

    #[Test]
    public function scope_by_table_filters_by_table()
    {
        $table1 = Table::factory()->create();
        $table2 = Table::factory()->create();

        Commande::factory()->create(['table_id' => $table1->id]);
        Commande::factory()->create(['table_id' => $table2->id]);

        $table1Orders = Commande::byTable($table1->id)->get();

        $this->assertCount(1, $table1Orders);
        $this->assertEquals($table1->id, $table1Orders->first()->table_id);
    }

    #[Test]
    public function it_calculates_total_from_details()
    {
        $commande = Commande::factory()->create(['total' => 0]);

        CommandeDetail::factory()->create([
            'commande_id' => $commande->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        CommandeDetail::factory()->create([
            'commande_id' => $commande->id,
            'quantity' => 1,
            'price' => 30.00,
        ]);

        $calculatedTotal = $commande->details->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });

        $this->assertEquals(130.00, $calculatedTotal);
    }

    #[Test]
    public function it_has_timestamps()
    {
        $commande = Commande::factory()->create();

        $this->assertNotNull($commande->created_at);
        $this->assertNotNull($commande->updated_at);
    }
}

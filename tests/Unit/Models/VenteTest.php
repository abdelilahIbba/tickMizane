<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\User;
use App\Models\Table;
use App\Models\Paiement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $vente = Vente::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $vente->user);
        $this->assertEquals($user->id, $vente->user->id);
    }

    #[Test]
    public function it_belongs_to_table()
    {
        $table = Table::factory()->create();
        $vente = Vente::factory()->create(['table_id' => $table->id]);

        $this->assertInstanceOf(Table::class, $vente->table);
        $this->assertEquals($table->id, $vente->table->id);
    }

    #[Test]
    public function it_has_many_details()
    {
        $vente = Vente::factory()->create();
        VenteDetail::factory()->count(3)->create(['vente_id' => $vente->id]);

        $this->assertCount(3, $vente->details);
        $this->assertInstanceOf(VenteDetail::class, $vente->details->first());
    }

    #[Test]
    public function it_has_many_paiements()
    {
        $vente = Vente::factory()->create();
        Paiement::factory()->count(2)->create(['vente_id' => $vente->id]);

        $this->assertCount(2, $vente->paiements);
        $this->assertInstanceOf(Paiement::class, $vente->paiements->first());
    }

    #[Test]
    public function it_casts_total_to_decimal()
    {
        $vente = Vente::factory()->create(['total' => 250.75]);

        $this->assertIsString($vente->total);
        $this->assertEquals('250.75', $vente->total);
    }

    #[Test]
    public function it_identifies_paid_status()
    {
        $paidVente = Vente::factory()->create(['status' => 'paid']);
        $unpaidVente = Vente::factory()->create(['status' => 'unpaid']);

        $this->assertTrue($paidVente->isPaid());
        $this->assertFalse($unpaidVente->isPaid());
    }

    #[Test]
    public function it_identifies_unpaid_status()
    {
        $unpaidVente = Vente::factory()->create(['status' => 'unpaid']);
        $paidVente = Vente::factory()->create(['status' => 'paid']);

        $this->assertTrue($unpaidVente->isUnpaid());
        $this->assertFalse($paidVente->isUnpaid());
    }

    #[Test]
    public function scope_paid_returns_only_paid_ventes()
    {
        Vente::factory()->create(['status' => 'paid']);
        Vente::factory()->create(['status' => 'unpaid']);
        Vente::factory()->create(['status' => 'paid']);

        $paidVentes = Vente::paid()->get();

        $this->assertCount(2, $paidVentes);
    }

    #[Test]
    public function scope_unpaid_returns_only_unpaid_ventes()
    {
        Vente::factory()->create(['status' => 'paid']);
        Vente::factory()->create(['status' => 'unpaid']);
        Vente::factory()->create(['status' => 'unpaid']);

        $unpaidVentes = Vente::unpaid()->get();

        $this->assertCount(2, $unpaidVentes);
    }

    #[Test]
    public function scope_today_returns_todays_ventes()
    {
        Vente::factory()->create(['created_at' => now()]);
        Vente::factory()->create(['created_at' => now()->subDay()]);

        $todayVentes = Vente::today()->get();

        $this->assertCount(1, $todayVentes);
    }

    #[Test]
    public function scope_by_user_filters_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Vente::factory()->create(['user_id' => $user1->id]);
        Vente::factory()->create(['user_id' => $user2->id]);

        $user1Ventes = Vente::byUser($user1->id)->get();

        $this->assertCount(1, $user1Ventes);
        $this->assertEquals($user1->id, $user1Ventes->first()->user_id);
    }

    #[Test]
    public function it_can_be_associated_with_table()
    {
        $table = Table::factory()->create();
        $vente = Vente::factory()->create(['table_id' => $table->id]);

        $this->assertNotNull($vente->table_id);
        $this->assertEquals($table->name, $vente->table->name);
    }

    #[Test]
    public function it_can_be_standalone_without_table()
    {
        $vente = Vente::factory()->create(['table_id' => null]);

        $this->assertNull($vente->table_id);
        $this->assertNull($vente->table);
    }

    #[Test]
    public function it_stores_payment_method()
    {
        $venteCard = Vente::factory()->create(['payment_method' => 'carte']);
        $venteCash = Vente::factory()->create(['payment_method' => 'cash']);
        $venteMixed = Vente::factory()->create(['payment_method' => 'mixte']);

        $this->assertEquals('carte', $venteCard->payment_method);
        $this->assertEquals('cash', $venteCash->payment_method);
        $this->assertEquals('mixte', $venteMixed->payment_method);
    }

    #[Test]
    public function it_has_timestamps()
    {
        $vente = Vente::factory()->create();

        $this->assertNotNull($vente->created_at);
        $this->assertNotNull($vente->updated_at);
    }
}

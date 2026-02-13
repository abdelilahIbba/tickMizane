<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Paiement;
use App\Models\Vente;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaiementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_vente()
    {
        $vente = Vente::factory()->create();
        $paiement = Paiement::factory()->create([
            'vente_id' => $vente->id,
        ]);

        $this->assertInstanceOf(Vente::class, $paiement->vente);
        $this->assertEquals($vente->id, $paiement->vente->id);
    }

    #[Test]
    public function it_belongs_to_commande()
    {
        $commande = Commande::factory()->create();
        $paiement = Paiement::factory()->create([
            'commande_id' => $commande->id,
        ]);

        $this->assertInstanceOf(Commande::class, $paiement->commande);
        $this->assertEquals($commande->id, $paiement->commande->id);
    }

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $paiement = Paiement::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $paiement->user);
        $this->assertEquals($user->id, $paiement->user->id);
    }

    #[Test]
    public function it_casts_amount_to_decimal()
    {
        $paiement = Paiement::factory()->create([
            'amount' => 150.50,
        ]);

        $this->assertIsString($paiement->amount);
        $this->assertEquals('150.50', $paiement->amount);
    }

    #[Test]
    public function scope_by_status_filters_correctly()
    {
        Paiement::factory()->create(['status' => 'completed']);
        Paiement::factory()->create(['status' => 'pending']);
        Paiement::factory()->create(['status' => 'completed']);

        $completed = Paiement::byStatus('completed')->get();

        $this->assertCount(2, $completed);
    }

    #[Test]
    public function scope_completed_returns_only_completed_payments()
    {
        Paiement::factory()->create(['status' => 'completed']);
        Paiement::factory()->create(['status' => 'pending']);

        $completed = Paiement::completed()->get();

        $this->assertCount(1, $completed);
        $this->assertEquals('completed', $completed->first()->status);
    }

    #[Test]
    public function scope_for_commande_filters_by_commande()
    {
        $commande1 = Commande::factory()->create();
        $commande2 = Commande::factory()->create();
        
        Paiement::factory()->create([
            'commande_id' => $commande1->id,
            'vente_id' => null,
        ]);
        Paiement::factory()->create([
            'commande_id' => $commande2->id,
            'vente_id' => null,
        ]);

        $payments = Paiement::forCommande($commande1->id)->get();

        $this->assertCount(1, $payments);
        $this->assertEquals($commande1->id, $payments->first()->commande_id);
    }

    #[Test]
    public function scope_for_vente_filters_by_vente()
    {
        $vente1 = Vente::factory()->create();
        $vente2 = Vente::factory()->create();
        
        $payment1 = Paiement::factory()->create(['vente_id' => $vente1->id, 'commande_id' => null]);
        $payment2 = Paiement::factory()->create(['vente_id' => $vente2->id, 'commande_id' => null]);

        $payments = Paiement::forVente($vente1->id)->get();

        $this->assertCount(1, $payments);
        $this->assertEquals($vente1->id, $payments->first()->vente_id);
    }

    #[Test]
    public function it_can_store_nullable_fields()
    {
        $paiement = Paiement::factory()->create([
            'commande_id' => null,
            'vente_id' => null,
            'reference' => null,
            'notes' => null,
        ]);

        $this->assertNull($paiement->commande_id);
        $this->assertNull($paiement->vente_id);
        $this->assertNull($paiement->reference);
        $this->assertNull($paiement->notes);
    }

    #[Test]
    public function it_stores_reference_and_notes()
    {
        $paiement = Paiement::factory()->create([
            'reference' => 'PAY-2026-001',
            'notes' => 'Cash payment with change',
        ]);

        $this->assertEquals('PAY-2026-001', $paiement->reference);
        $this->assertEquals('Cash payment with change', $paiement->notes);
    }

    #[Test]
    public function it_timestamps_are_automatically_managed()
    {
        $paiement = Paiement::factory()->create();

        $this->assertNotNull($paiement->created_at);
        $this->assertNotNull($paiement->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $paiement->created_at);
    }
}

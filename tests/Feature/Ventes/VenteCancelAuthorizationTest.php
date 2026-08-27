<?php

namespace Tests\Feature\Ventes;

use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VenteCancelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function caissier_cannot_cancel_a_sale(): void
    {
        /** @var User $caissier */
        $caissier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $vente = Vente::factory()->create([
            'status' => 'paid',
        ]);

        $response = $this->actingAs($caissier)
            ->post(route('ventes.cancel', $vente));

        $response->assertRedirect(route('kitchen.index'));
        $this->assertNotEquals('cancelled', $vente->fresh()->status);
    }

    #[Test]
    public function admin_can_cancel_a_sale(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $vente = Vente::factory()->create([
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('ventes.index'))
            ->post(route('ventes.cancel', $vente), [
                'comment' => 'Produit manquant au moment du paiement.',
            ]);

        $response->assertRedirect(route('ventes.index'));
        $this->assertEquals('cancelled', $vente->fresh()->status);
        $this->assertSame('Produit manquant au moment du paiement.', $vente->fresh()->cancel_reason);
        $this->assertDatabaseHas('ventes', [
            'id' => $vente->id,
            'status' => 'cancelled',
            'cancel_reason' => 'Produit manquant au moment du paiement.',
        ]);
    }

    #[Test]
    public function admin_must_provide_a_comment_to_cancel_a_sale(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $vente = Vente::factory()->create([
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('ventes.index'))
            ->post(route('ventes.cancel', $vente), []);

        $response->assertSessionHasErrors('comment');
        $this->assertNotEquals('cancelled', $vente->fresh()->status);
    }
}

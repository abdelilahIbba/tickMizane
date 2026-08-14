<?php

namespace Tests\Feature\Ventes;

use App\Models\Produit;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vente;
use App\Models\VenteDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VenteCancelSideEffectsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cancelling_paid_sale_restores_stock_and_excludes_from_paid_scope(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $product = Produit::factory()->create([
            'stock_quantity' => 7,
            'status' => 'active',
        ]);

        $vente = Vente::factory()->create([
            'user_id' => $admin->id,
            'status' => 'paid',
            'total' => 100,
            'payment_method' => 'cash',
        ]);

        VenteDetail::factory()->create([
            'vente_id' => $vente->id,
            'produit_id' => $product->id,
            'quantity' => 3,
            'price' => 20,
            'total_line' => 60,
        ]);

        $this->actingAs($admin)
            ->post(route('ventes.cancel', $vente))
            ->assertRedirect(route('ventes.index'))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $vente->fresh()->status);
        $this->assertSame(10, (int) $product->fresh()->stock_quantity);
        $this->assertTrue(
            StockMovement::where('produit_id', $product->id)
                ->where('type', 'in')
                ->where('quantity', 3)
                ->exists()
        );
        $this->assertFalse(Vente::paid()->where('id', $vente->id)->exists());
    }

    #[Test]
    public function cannot_cancel_already_cancelled_sale(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $vente = Vente::factory()->create([
            'status' => 'cancelled',
            'total' => 50,
        ]);

        $this->actingAs($admin)
            ->from(route('ventes.index'))
            ->post(route('ventes.cancel', $vente))
            ->assertRedirect(route('ventes.index'))
            ->assertSessionHas('error');
    }
}

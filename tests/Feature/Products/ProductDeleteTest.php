<?php

namespace Tests\Feature\Products;

use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_delete_when_product_is_only_in_unpaid_sales(): void
    {
        $this->actingAsAdmin();

        $product = Produit::factory()->create();

        $vente = Vente::factory()->unpaid()->create();
        $detail = VenteDetail::factory()
            ->forVente($vente)
            ->forProduct($product)
            ->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('produits', ['id' => $product->id]);
        $this->assertDatabaseMissing('vente_details', ['id' => $detail->id]);
    }

    public function test_it_allows_delete_when_product_is_only_in_cancelled_sales(): void
    {
        $this->actingAsAdmin();

        $product = Produit::factory()->create();

        $vente = Vente::factory()->create(['status' => 'cancelled']);
        $detail = VenteDetail::factory()
            ->forVente($vente)
            ->forProduct($product)
            ->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('produits', ['id' => $product->id]);
        $this->assertDatabaseMissing('vente_details', ['id' => $detail->id]);
    }

    public function test_it_blocks_delete_when_product_has_paid_sales(): void
    {
        $this->actingAsAdmin();

        $product = Produit::factory()->create();

        $vente = Vente::factory()->paid()->create();
        VenteDetail::factory()
            ->forVente($vente)
            ->forProduct($product)
            ->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('error', 'Impossible de supprimer ce produit car il a des ventes associées.');

        $this->assertDatabaseHas('produits', ['id' => $product->id]);
    }
}

<?php

namespace Tests\Feature\ClientOrder;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Category;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for GET /order — the public QR-code menu page.
 *
 * This endpoint is unauthenticated and must work for restaurant tables,
 * pool-side guests, and hotel room service clients.
 */
class ClientOrderMenuTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Page rendering
    // -----------------------------------------------------------------------

    #[Test]
    public function menu_page_returns_200_for_unauthenticated_guests(): void
    {
        $response = $this->get(route('client.order.menu'));

        $response->assertStatus(200);
        $response->assertViewIs('client.order');
    }

    #[Test]
    public function menu_page_passes_menu_data_to_view(): void
    {
        $category = Category::factory()->create(['name' => 'Boissons', 'status' => 'active']);
        Produit::factory()->create([
            'category_id'  => $category->id,
            'name'         => 'Café Noir',
            'price_vente'  => 15.00,
            'status'       => 'active',
        ]);

        $response = $this->get(route('client.order.menu'));

        $response->assertViewHas('menuData');
        $menuData = $response->viewData('menuData');

        $this->assertNotEmpty($menuData);
        $this->assertEquals('Boissons', $menuData->first()['name']);
    }

    #[Test]
    public function menu_page_passes_location_type_from_query_string(): void
    {
        $response = $this->get(route('client.order.menu', ['type' => 'pool']));

        $response->assertViewHas('locationType', 'pool');
    }

    #[Test]
    public function menu_page_location_type_is_null_when_not_provided(): void
    {
        $response = $this->get(route('client.order.menu'));

        $response->assertViewHas('locationType', null);
    }

    // -----------------------------------------------------------------------
    // Category / product filtering
    // -----------------------------------------------------------------------

    #[Test]
    public function inactive_categories_are_excluded_from_menu(): void
    {
        Category::factory()->create(['name' => 'Visible',  'status' => 'active']);
        Category::factory()->create(['name' => 'Archived', 'status' => 'archived']);

        $response   = $this->get(route('client.order.menu'));
        $menuData   = $response->viewData('menuData');
        $names      = $menuData->pluck('name');

        $this->assertContains('Visible',  $names->all());
        $this->assertNotContains('Archived', $names->all());
    }

    #[Test]
    public function inactive_products_are_excluded_from_their_category(): void
    {
        $category = Category::factory()->create(['status' => 'active']);
        Produit::factory()->create(['category_id' => $category->id, 'name' => 'Actif',   'status' => 'active']);
        Produit::factory()->create(['category_id' => $category->id, 'name' => 'Inactif', 'status' => 'inactive']);

        $response   = $this->get(route('client.order.menu'));
        $menuData   = $response->viewData('menuData');
        $products   = collect($menuData->first()['products']);

        $this->assertCount(1, $products);
        $this->assertEquals('Actif', $products->first()['name']);
    }

    #[Test]
    public function each_menu_product_contains_required_fields(): void
    {
        $category = Category::factory()->create(['status' => 'active']);
        Produit::factory()->create([
            'category_id' => $category->id,
            'name'        => 'Tagine',
            'price_vente' => 85.00,
            'unit'        => 'pcs',
            'status'      => 'active',
        ]);

        $response = $this->get(route('client.order.menu'));
        $product  = collect($response->viewData('menuData')->first()['products'])->first();

        $this->assertArrayHasKey('id',    $product);
        $this->assertArrayHasKey('name',  $product);
        $this->assertArrayHasKey('price', $product);
        $this->assertArrayHasKey('image', $product);
        $this->assertArrayHasKey('unit',  $product);

        $this->assertSame('Tagine', $product['name']);
        $this->assertSame(85.0,     $product['price']);
        $this->assertSame('pcs',    $product['unit']);
    }

    #[Test]
    public function menu_page_is_empty_when_no_active_categories_exist(): void
    {
        Category::factory()->create(['status' => 'archived']);

        $response  = $this->get(route('client.order.menu'));
        $menuData  = $response->viewData('menuData');

        $this->assertCount(0, $menuData);
    }

    #[Test]
    public function categories_with_no_active_products_still_appear_in_menu(): void
    {
        $category = Category::factory()->create(['name' => 'Empty Cat', 'status' => 'active']);
        Produit::factory()->create(['category_id' => $category->id, 'status' => 'inactive']);

        $response  = $this->get(route('client.order.menu'));
        $menuData  = $response->viewData('menuData');

        $this->assertCount(1, $menuData);
        $this->assertCount(0, $menuData->first()['products']);
    }
}

<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_products_and_creates_categories_from_xls(): void
    {
        $this->actingAsAdmin();

        $fixturePath = base_path('Menu Temporaire V 4.xls');
        $this->assertFileExists($fixturePath);

        $response = $this->post(route('products.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('menu.xls', file_get_contents($fixturePath)),
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Boissons']);
        $this->assertDatabaseHas('categories', ['name' => 'Les Petits Plaisirs']);

        $category = Category::where('name', 'Boissons')->firstOrFail();
        $product = Produit::where('category_id', $category->id)
            ->where('name', 'Eau 50 cl')
            ->firstOrFail();

        $this->assertSame(100, $product->stock_quantity);
        $this->assertSame('active', $product->status);
        $this->assertSame('bouteille', $product->unit);

        $this->assertDatabaseHas('stock_movements', [
            'produit_id' => $product->id,
            'type' => 'in',
            'quantity' => 100,
            'reason' => 'ajustement',
        ]);

        $this->assertGreaterThan(20, Produit::count());
    }

    public function test_it_skips_existing_product_without_modifying_it(): void
    {
        $this->actingAsAdmin();

        $category = Category::create([
            'name' => 'Boissons',
            'status' => 'active',
        ]);

        $existing = Produit::create([
            'category_id' => $category->id,
            'name' => 'Eau 50 cl',
            'price_vente' => 1,
            'price_achat' => 0.4,
            'stock_quantity' => 7,
            'alert_stock' => 2,
            'unit' => 'pcs',
            'status' => 'inactive',
            'kitchen_active' => true,
        ]);

        $fixturePath = base_path('Menu Temporaire V 4.xls');

        $response = $this->post(route('products.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('menu.xls', file_get_contents($fixturePath)),
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $existing->refresh();

        $this->assertSame(7, $existing->stock_quantity);
        $this->assertSame('inactive', $existing->status);
        $this->assertSame('1.00', $existing->price_vente);
        $this->assertSame('pcs', $existing->unit);

        $this->assertSame(1, Produit::where('category_id', $category->id)->where('name', 'Eau 50 cl')->count());

        $response->assertSessionHas('success', function (string $message): bool {
            return str_contains($message, 'ignoré(s) car déjà existant(s)');
        });
    }

    public function test_it_rejects_non_excel_file_upload(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('products.import'), [
            'import_file' => UploadedFile::fake()->create('invalid.txt', 10, 'text/plain'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHasErrorsIn('productImport', ['import_file']);
    }
}

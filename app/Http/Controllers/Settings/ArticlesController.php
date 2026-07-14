<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Produit;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticlesController extends Controller
{
    /* ──────────────────────────────────────────────────
     | Main page
     ────────────────────────────────────────────────── */

    public function index(Request $request)
    {
        $categories = Category::withCount('produits')->orderBy('name')->get();

        $stats = [
            'categories'      => $categories->count(),
            'active_cats'     => $categories->where('status', 'active')->count(),
            'products'        => Produit::count(),
            'active_products' => Produit::where('status', 'active')->count(),
        ];

        // Optionally pre-load one category's products
        $selectedCategoryId = $request->integer('category', 0);
        $selectedCategory   = $selectedCategoryId
            ? Category::with('produits')->find($selectedCategoryId)
            : null;

        return view('settings.articles.index', compact('categories', 'stats', 'selectedCategory'));
    }

    /* ──────────────────────────────────────────────────
     | Category CRUD
     ────────────────────────────────────────────────── */

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|in:active,archived',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'   => 'nullable|url|max:2048',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $image = $this->resolveImage($request, 'categories');
        if ($image) {
            $validated['image'] = $image;
        }
        unset($validated['image_file'], $validated['image_url']);

        Category::create($validated);

        return redirect()->route('settings.articles.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|in:active,archived',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'   => 'nullable|url|max:2048',
        ]);

        $newImage = $this->resolveImage($request, 'categories');
        if ($newImage) {
            $this->deleteLocalImage($category->image);
            $validated['image'] = $newImage;
        }
        unset($validated['image_file'], $validated['image_url']);

        $category->update($validated);

        return redirect()->route('settings.articles.index', ['category' => $category->id])
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->produits()->exists()) {
            return redirect()->route('settings.articles.index')
                ->with('error', 'Impossible de supprimer : cette catégorie contient des produits.');
        }

        $this->deleteLocalImage($category->image);
        $category->delete();

        return redirect()->route('settings.articles.index')
            ->with('success', 'Catégorie supprimée.');
    }

    /* ──────────────────────────────────────────────────
     | Product CRUD (within a category)
     ────────────────────────────────────────────────── */

    public function storeProduct(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'price_vente'    => 'required|numeric|min:0',
            'price_achat'    => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_stock'    => 'nullable|integer|min:0',
            'unit'           => 'nullable|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'kitchen_active' => 'nullable|boolean',
            'image_file'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'      => 'nullable|url|max:2048',
        ]);

        $validated['category_id'] = $category->id;
        $validated['alert_stock'] = $validated['alert_stock'] ?? 10;
        $validated['status']      = $validated['status'] ?? 'active';
        $validated['unit']        = $validated['unit'] ?? 'portion';
        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $image = $this->resolveImage($request, 'products');
        if ($image) {
            $validated['image'] = $image;
        }
        unset($validated['image_file'], $validated['image_url']);

        $produit = Produit::create($validated);

        if ($produit->stock_quantity > 0) {
            StockMovement::create([
                'produit_id' => $produit->id,
                'type'       => 'in',
                'quantity'   => $produit->stock_quantity,
                'reason'     => 'ajustement',
            ]);
        }

        return redirect()->route('settings.articles.index', ['category' => $category->id])
            ->with('success', 'Article créé avec succès.');
    }

    public function updateProduct(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'price_vente' => 'required|numeric|min:0',
            'price_achat' => 'nullable|numeric|min:0',
            'alert_stock' => 'nullable|integer|min:0',
            'unit'        => 'nullable|string|max:50',
            'status'      => 'nullable|in:active,inactive',
            'kitchen_active' => 'nullable|boolean',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'   => 'nullable|url|max:2048',
        ]);

        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $newImage = $this->resolveImage($request, 'products');
        if ($newImage) {
            $this->deleteLocalImage($product->image);
            $validated['image'] = $newImage;
        }
        unset($validated['image_file'], $validated['image_url']);

        $product->update($validated);

        return redirect()->route('settings.articles.index', ['category' => $product->category_id])
            ->with('success', 'Article mis à jour.');
    }

    public function destroyProduct(Produit $product)
    {
        $categoryId = $product->category_id;
        $this->deleteLocalImage($product->image);
        $product->delete();

        return redirect()->route('settings.articles.index', ['category' => $categoryId])
            ->with('success', 'Article supprimé.');
    }

    /* ──────────────────────────────────────────────────
     | Helpers
     ────────────────────────────────────────────────── */

    private function resolveImage(Request $request, string $folder): ?string
    {
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $path = $request->file('image_file')->store($folder, 'public');
            return $path;
        }

        if ($request->filled('image_url')) {
            return $request->input('image_url');
        }

        return null;
    }

    private function deleteLocalImage(?string $image): void
    {
        if (empty($image) || Str::startsWith($image, ['http://', 'https://'])) {
            return;
        }
        Storage::disk('public')->delete($image);
    }
}

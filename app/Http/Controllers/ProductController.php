<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Category;
use App\Models\StockMovement;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Produit::with('category');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Stock filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereRaw('stock_quantity <= alert_stock');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        $produits = $query->orderBy('name')->paginate(15);
        $categories = Category::orderBy('name')->get();

        // Stats
        $stats = [
            'total' => Produit::count(),
            'active' => Produit::where('status', 'active')->count(),
            'low_stock' => Produit::whereRaw('stock_quantity <= alert_stock')->count(),
            'out_of_stock' => Produit::where('stock_quantity', 0)->count(),
        ];

        return view('products.index', compact('produits', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price_achat' => 'nullable|numeric|min:0',
            'price_vente' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|in:pcs,kg,l',
            'status' => 'nullable|in:active,inactive',
            'kitchen_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:2048',
        ]);

        $validated['alert_stock'] = $validated['alert_stock'] ?? 10;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['unit'] = $validated['unit'] ?? 'pcs';
        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $image = $this->resolveImageInput($request, 'products');
        if ($image !== null) {
            $validated['image'] = $image;
        }

        unset($validated['image_file'], $validated['image_url']);

        $produit = Produit::create($validated);

        // Create initial stock movement if stock > 0
        if ($produit->stock_quantity > 0) {
            StockMovement::create([
                'produit_id' => $produit->id,
                'type' => 'in',
                'quantity' => $produit->stock_quantity,
                'reason' => 'ajustement',
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    /**
     * Display the specified product.
     */
    public function show(Produit $product)
    {
        $product->load(['category', 'stockMovements' => function ($query) {
            $query->latest()->limit(20);
        }]);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the product.
     */
    public function edit(Produit $product)
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price_achat' => 'nullable|numeric|min:0',
            'price_vente' => 'required|numeric|min:0',
            'alert_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|in:pcs,kg,l',
            'status' => 'nullable|in:active,inactive',
            'kitchen_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:2048',
        ]);

        $validated['kitchen_active'] = $request->boolean('kitchen_active', true);

        $newImage = $this->resolveImageInput($request, 'products');
        if ($newImage !== null) {
            $this->deleteLocalImageIfExists($product->image);
            $validated['image'] = $newImage;
        }

        unset($validated['image_file'], $validated['image_url']);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Produit $product)
    {
        // Block deletion only when product has paid sales history.
        if ($product->hasPaidSales()) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Impossible de supprimer ce produit car il a des ventes associées.');
        }

        // Delete stock movements
        $product->stockMovements()->delete();
        $this->deleteLocalImageIfExists($product->image);
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }

    /**
     * Update product stock.
     */
    public function updateStock(Request $request, Produit $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
        ]);

        $isAddition = $validated['type'] === 'in';

        if (!$isAddition && $product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Stock insuffisant pour cette opération.');
        }

        $stockApres = $isAddition 
            ? $product->stock_quantity + $validated['quantity'] 
            : $product->stock_quantity - $validated['quantity'];

        // Create stock movement
        StockMovement::create([
            'produit_id' => $product->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'reason' => 'ajustement',
        ]);

        // Update product stock
        $product->update(['stock_quantity' => $stockApres]);

        return back()->with('success', 'Stock mis à jour avec succès.');
    }

    /**
     * Import products from an Excel file (.xls).
     */
    public function import(Request $request, ProductImportService $importService)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:10240',
        ], [
            'import_file.required' => 'Veuillez sélectionner un fichier Excel.',
            'import_file.mimes' => 'Le fichier doit être au format Excel (.xls ou .xlsx).',
            'import_file.max' => 'Le fichier dépasse la taille maximale autorisée (10 MB).',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'productImport')
                ->with('error', 'Import impossible: fichier invalide.')
                ->with('open_product_import_modal', true);
        }

        try {
            $stats = $importService->importFromXls($request->file('import_file')->getRealPath());
        } catch (RuntimeException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('open_product_import_modal', true);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->with('error', 'Une erreur est survenue pendant l\'import. Veuillez réessayer.')
                ->with('open_product_import_modal', true);
        }

        $message = sprintf(
            'Import terminé: %d produit(s) créé(s), %d ignoré(s) car déjà existant(s), %d catégorie(s) créée(s) sur %d ligne(s) traitée(s).',
            $stats['products_created'],
            $stats['products_skipped'],
            $stats['categories_created'],
            $stats['rows_processed']
        );

        return redirect()
            ->route('products.index')
            ->with('success', $message);
    }

    /**
     * Resolve image from upload or URL input.
     */
    private function resolveImageInput(Request $request, string $folder): ?string
    {
        if ($request->hasFile('image_file')) {
            return $request->file('image_file')->store($folder, 'public');
        }

        if ($request->filled('image_url')) {
            return trim((string) $request->input('image_url'));
        }

        return null;
    }

    /**
     * Delete local storage image if present.
     */
    private function deleteLocalImageIfExists(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

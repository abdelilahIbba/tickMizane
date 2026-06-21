<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('produits');

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('name')->paginate(15);

        // Stats
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', 'active')->count(),
            'with_products' => Category::has('produits')->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,archived',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url|max:2048',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $image = $this->resolveImageInput($request, 'categories');
        if ($image !== null) {
            $validated['image'] = $image;
        }

        unset($validated['image_file'], $validated['image_url']);

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $category->load(['produits' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,archived',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url|max:2048',
        ]);

        $newImage = $this->resolveImageInput($request, 'categories');
        if ($newImage !== null) {
            $this->deleteLocalImageIfExists($category->image);
            $validated['image'] = $newImage;
        }

        unset($validated['image_file'], $validated['image_url']);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->produits()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
        }

        $this->deleteLocalImageIfExists($category->image);
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category)
    {
        $newStatus = $category->status === 'active' ? 'archived' : 'active';
        $category->update(['status' => $newStatus]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Statut de la catégorie mis à jour.');
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

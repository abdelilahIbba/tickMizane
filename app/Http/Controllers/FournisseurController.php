<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    /**
     * Display a listing of fournisseurs.
     */
    public function index(Request $request)
    {
        $query = Fournisseur::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $fournisseurs = $query->withCount('commandes')
                              ->orderBy('name')
                              ->paginate(15);

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    /**
     * Show the form for creating a new fournisseur.
     */
    public function create()
    {
        return view('fournisseurs.create');
    }

    /**
     * Store a newly created fournisseur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:fournisseurs,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
        ]);

        Fournisseur::create($validated);

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    /**
     * Show the form for editing the fournisseur.
     */
    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    /**
     * Update the specified fournisseur.
     */
    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
        ]);

        $fournisseur->update($validated);

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', 'Fournisseur mis à jour avec succès.');
    }

    /**
     * Display the specified fournisseur.
     */
    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load(['commandes' => function ($query) {
            $query->with(['details.produit', 'user'])->latest()->take(10);
        }]);

        // Get statistics
        $stats = [
            'total_commandes' => $fournisseur->commandes()->count(),
            'pending_commandes' => $fournisseur->commandes()->pending()->count(),
            'received_commandes' => $fournisseur->commandes()->received()->count(),
            'total_value' => $fournisseur->commandes()->sum('total'),
        ];

        return view('fournisseurs.show', compact('fournisseur', 'stats'));
    }

    /**
     * Remove the specified fournisseur.
     */
    public function destroy(Fournisseur $fournisseur)
    {
        // Check if fournisseur has commandes
        if ($fournisseur->commandes()->exists()) {
            return redirect()
                ->route('fournisseurs.index')
                ->with('error', 'Impossible de supprimer ce fournisseur car il a des commandes associées.');
        }

        $fournisseur->delete();

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', 'Fournisseur supprimé avec succès.');
    }
}


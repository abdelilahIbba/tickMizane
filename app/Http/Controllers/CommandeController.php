<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\StockMovement;
use App\Support\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    /**
     * Display a listing of commandes (supplier orders).
     */
    public function index(Request $request)
    {
        $query = Commande::with(['fournisseur', 'user', 'details']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by fournisseur
        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        $commandes = $query->latest()->paginate(15);
        $fournisseurs = Fournisseur::orderBy('name')->get();

        // Stats
        $stats = [
            'pending' => Commande::where('status', 'pending')->count(),
            'received' => Commande::where('status', 'received')->count(),
            'total_value' => Commande::where('status', 'received')->sum('total'),
        ];

        return view('commandes.index', compact('commandes', 'fournisseurs', 'stats'));
    }

    /**
     * Show the form for creating a new commande.
     */
    public function create()
    {
        $fournisseurs = Fournisseur::orderBy('name')->get();
        $produits = Produit::orderBy('name')->get();
        
        return view('commandes.create', compact('fournisseurs', 'produits'));
    }

    /**
     * Store a newly created commande.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'notes' => 'nullable|string|max:500',
            'produits' => 'required|array|min:1',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_achat' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate reference
            $reference = 'CMD-' . date('Ymd') . '-' . str_pad(Commande::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total
            $total = collect($validated['produits'])->sum(function ($item) {
                return $item['quantite'] * $item['prix_achat'];
            });

            // Create commande
            $commande = Commande::create([
                'reference' => $reference,
                'fournisseur_id' => $validated['fournisseur_id'],
                'user_id' => SuperAdmin::databaseUserId(),
                'statut' => 'en_attente',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create commande details
            foreach ($validated['produits'] as $item) {
                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item['id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_achat'],
                    'sous_total' => $item['quantite'] * $item['prix_achat'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('commandes.show', $commande)
                ->with('success', 'Commande créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la commande: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified commande.
     */
    public function show(Commande $commande)
    {
        $commande->load(['fournisseur', 'user', 'details.produit']);
        return view('commandes.show', compact('commande'));
    }

    /**
     * Show the form for editing the commande.
     */
    public function edit(Commande $commande)
    {
        if ($commande->statut !== 'en_attente') {
            return redirect()
                ->route('commandes.show', $commande)
                ->with('error', 'Impossible de modifier une commande déjà reçue ou annulée.');
        }

        $fournisseurs = Fournisseur::orderBy('name')->get();
        $produits = Produit::orderBy('name')->get();
        $commande->load('details.produit');

        return view('commandes.edit', compact('commande', 'fournisseurs', 'produits'));
    }

    /**
     * Update the specified commande.
     */
    public function update(Request $request, Commande $commande)
    {
        if ($commande->statut !== 'en_attente') {
            return redirect()
                ->route('commandes.show', $commande)
                ->with('error', 'Impossible de modifier une commande déjà reçue ou annulée.');
        }

        $validated = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'notes' => 'nullable|string|max:500',
            'produits' => 'required|array|min:1',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_achat' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate new total
            $total = collect($validated['produits'])->sum(function ($item) {
                return $item['quantite'] * $item['prix_achat'];
            });

            // Update commande
            $commande->update([
                'fournisseur_id' => $validated['fournisseur_id'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete old details and create new ones
            $commande->details()->delete();

            foreach ($validated['produits'] as $item) {
                CommandeDetail::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item['id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_achat'],
                    'sous_total' => $item['quantite'] * $item['prix_achat'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('commandes.show', $commande)
                ->with('success', 'Commande mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Receive the commande and update stock.
     */
    public function receive(Commande $commande)
    {
        if ($commande->statut !== 'en_attente') {
            return redirect()
                ->route('commandes.show', $commande)
                ->with('error', 'Cette commande a déjà été traitée.');
        }

        DB::beginTransaction();
        try {
            // Update stock for each product
            foreach ($commande->details as $detail) {
                $produit = $detail->produit;
                
                // Create stock movement
                StockMovement::create([
                    'produit_id' => $produit->id,
                    'type' => 'achat',
                    'quantite' => $detail->quantite,
                    'stock_avant' => $produit->stock,
                    'stock_apres' => $produit->stock + $detail->quantite,
                    'reference' => $commande->reference,
                    'notes' => 'Réception commande ' . $commande->reference,
                    'user_id' => SuperAdmin::databaseUserId(),
                ]);

                // Update product stock
                $produit->increment('stock', $detail->quantite);
            }

            // Update commande status
            $commande->update([
                'statut' => 'recue',
                'date_reception' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('commandes.show', $commande)
                ->with('success', 'Commande reçue et stock mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la réception: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified commande.
     */
    public function destroy(Commande $commande)
    {
        if ($commande->statut === 'recue') {
            return redirect()
                ->route('commandes.index')
                ->with('error', 'Impossible de supprimer une commande déjà reçue.');
        }

        $commande->details()->delete();
        $commande->delete();

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande supprimée avec succès.');
    }
}

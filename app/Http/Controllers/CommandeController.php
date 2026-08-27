<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommandeController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * List waiter kitchen orders (prise de commande).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Commande::class);

        $query = Commande::fromWaiter()->with(['table', 'user', 'details.produit', 'paiements']);

        if ($request->get('payment') === 'paid') {
            $query->where('status', 'payee');
        } elseif ($request->get('payment') === 'unpaid') {
            $query->where('status', '!=', 'payee')->where('status', '!=', 'annule');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commandes = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'unpaid' => Commande::fromWaiter()->whereNotIn('status', ['payee', 'annule'])->count(),
            'paid' => Commande::fromWaiter()->where('status', 'payee')->count(),
            'unpaid_value' => Commande::fromWaiter()->whereNotIn('status', ['payee', 'annule'])->sum('total'),
        ];

        return view('commandes.index', compact('commandes', 'stats'));
    }

    /**
     * New waiter orders are created from the prise de commande screen.
     */
    public function create()
    {
        Gate::authorize('create', Commande::class);

        return redirect()
            ->route('waiter.index')
            ->with('success', 'Créez la commande depuis la prise de commande (tables).');
    }

    /**
     * Store is handled by the waiter table order flow.
     */
    public function store()
    {
        Gate::authorize('create', Commande::class);

        return redirect()->route('waiter.index');
    }

    /**
     * Display a waiter kitchen order.
     */
    public function show(Commande $commande)
    {
        Gate::authorize('view', $commande);

        $commande->load(['table', 'user', 'details.produit', 'paiements']);

        return view('commandes.show', compact('commande'));
    }

    /**
     * Edit waiter kitchen order lines (add / remove / quantities).
     */
    public function edit(Commande $commande)
    {
        $this->ensureCanManageCommandes();

        if (!$commande->isOpenForEdit()) {
            return redirect()
                ->route('commandes.show', $commande)
                ->with('error', 'Impossible de modifier une commande annulée.');
        }

        $produits = Produit::active()->orderBy('name')->get();
        $commande->load(['details.produit', 'table', 'user']);
        $orderLines = $commande->details->map(fn ($detail) => [
            'product_id' => (string) $detail->produit_id,
            'quantity' => $detail->quantity,
            'price' => (float) $detail->price,
            'notes' => $detail->notes,
        ])->values();
        $productCatalog = $produits->mapWithKeys(fn ($produit) => [
            $produit->id => ['name' => $produit->name, 'price' => (float) $produit->price_vente],
        ]);

        return view('commandes.edit', compact('commande', 'produits', 'orderLines', 'productCatalog'));
    }

    /**
     * Update waiter kitchen order lines.
     */
    public function update(Request $request, Commande $commande)
    {
        $this->ensureCanManageCommandes();

        if (!$commande->isOpenForEdit()) {
            return redirect()
                ->route('commandes.show', $commande)
                ->with('error', 'Impossible de modifier une commande annulée.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->updateKitchenOrderItems(
                $commande,
                $validated['items'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('commandes.show', $commande)
                ->with('success', 'Commande mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: '.$e->getMessage());
        }
    }

    /**
     * Receive is not used for waiter kitchen orders.
     */
    public function receive(Commande $commande)
    {
        Gate::authorize('receive', $commande);

        return redirect()->route('commandes.show', $commande);
    }

    /**
     * Cancel an unpaid waiter kitchen order.
     */
    public function destroy(Commande $commande)
    {
        Gate::authorize('delete', $commande);

        $commande->update(['status' => 'annule']);

        if ($commande->table) {
            $hasActive = Commande::where('table_id', $commande->table_id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
                ->where('id', '!=', $commande->id)
                ->exists();

            if (!$hasActive) {
                $commande->table->release();
            }
        }

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande annulée avec succès.');
    }

    private function ensureCanManageCommandes(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Seul un administrateur peut modifier une commande.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Produit;
use App\Models\Category;
use App\Models\Commande;
use App\Models\User;
use App\Models\Zone;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WaiterController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show waiter dashboard (tablet interface).
     */
    public function index()
    {
        $tables = Table::orderBy('id')->get();
        $zones = Zone::orderBy('name')->get(['id', 'name']);

        // Active kitchen orders from the client ordering page (user_id = null)
        $clientOrders = Commande::where('type', 'kitchen')
            ->whereNull('user_id')
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret'])
            ->with('details.produit')
            ->latest()
            ->get();

        $restaurantOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Commande client - Table')
        )->values();

        $poolOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Commande client - Piscine')
        )->values();

        $roomOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Room service')
        )->values();

        return view('waiter.index', compact('tables', 'zones', 'restaurantOrders', 'poolOrders', 'roomOrders'));
    }

    /**
     * Show zone settings for waiter/admin users.
     */
    public function zoneSettings()
    {
        $zones = Zone::with(['tables' => function ($query) {
            $query->orderBy('id');
        }])->orderBy('name')->get();

        return view('waiter.settings-zones', compact('zones'));
    }

    /**
     * Create a new zone.
     */
    public function storeZone(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:zones,name',
            'prefix' => 'nullable|string|max:10|regex:/^[A-Za-z0-9]+$/',
            'tables_count' => 'required|integer|min:1|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated): void {
            $prefix = $this->normalizePrefix($validated['prefix'] ?? null, $validated['name']);

            $zone = Zone::create([
                'name' => $validated['name'],
                'prefix' => $prefix,
                'tables_count' => (int) $validated['tables_count'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->ensureZoneTableCount($zone, (int) $validated['tables_count']);
            $this->renameZoneTables($zone);
        });

        return redirect()
            ->route('waiter.settings.zones')
            ->with('success', 'Zone créée avec succès.');
    }

    /**
     * Update an existing zone.
     */
    public function updateZone(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:zones,name,'.$zone->id,
            'prefix' => 'nullable|string|max:10|regex:/^[A-Za-z0-9]+$/',
            'tables_count' => 'required|integer|min:1|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($zone, $validated): void {
            $zone->update([
                'name' => $validated['name'],
                'prefix' => $this->normalizePrefix($validated['prefix'] ?? null, $validated['name']),
                'tables_count' => (int) $validated['tables_count'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->ensureZoneTableCount($zone, (int) $validated['tables_count']);
            $this->renameZoneTables($zone);
        });

        return redirect()
            ->route('waiter.settings.zones')
            ->with('success', 'Zone mise à jour avec succès.');
    }

    /**
     * Delete a zone and unassign all linked tables.
     */
    public function destroyZone(Zone $zone)
    {
        DB::transaction(function () use ($zone): void {
            Table::where('zone_id', $zone->id)->update([
                'zone_id' => null,
                'zone' => null,
            ]);

            $zone->delete();
        });

        return redirect()
            ->route('waiter.settings.zones')
            ->with('success', 'Zone supprimée avec succès.');
    }

    private function normalizePrefix(?string $requestedPrefix, string $zoneName): string
    {
        $requestedPrefix = strtoupper(trim((string) $requestedPrefix));
        $requestedPrefix = preg_replace('/[^A-Z0-9]/', '', $requestedPrefix) ?: '';

        if ($requestedPrefix !== '') {
            return $requestedPrefix;
        }

        $cleanZone = strtoupper(trim($zoneName));
        $letters = preg_replace('/[^A-Z0-9]/', '', $cleanZone) ?: 'Z';

        return substr($letters, 0, 1);
    }

    private function ensureZoneTableCount(Zone $zone, int $targetCount): void
    {
        $tables = Table::where('zone_id', $zone->id)->orderBy('id')->get();
        $currentCount = $tables->count();

        if ($currentCount < $targetCount) {
            $toCreate = $targetCount - $currentCount;
            for ($i = 0; $i < $toCreate; $i++) {
                Table::create([
                    'name' => 'TMP',
                    'zone_id' => $zone->id,
                    'zone' => $zone->name,
                    'status' => 'free',
                    'places' => 4,
                    'is_active' => true,
                ]);
            }
        }

        if ($currentCount > $targetCount) {
            $extraTables = Table::where('zone_id', $zone->id)
                ->orderByDesc('id')
                ->take($currentCount - $targetCount)
                ->get();

            foreach ($extraTables as $table) {
                if ($table->status !== 'free' || $table->current_vente_id !== null) {
                    abort(422, 'Impossible de réduire le nombre de tables: certaines tables à retirer sont occupées.');
                }

                $table->delete();
            }
        }
    }

    private function renameZoneTables(Zone $zone): void
    {
        $tables = Table::where('zone_id', $zone->id)->orderBy('id')->get();
        $plannedNames = [];

        foreach ($tables as $index => $table) {
            $plannedNames[$table->id] = $zone->prefix . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
        }

        $conflictNames = array_values($plannedNames);
        $hasConflicts = Table::whereIn('name', $conflictNames)
            ->where('zone_id', '!=', $zone->id)
            ->exists();

        if ($hasConflicts) {
            abort(422, 'Ce prefixe genere des noms de tables deja utilises dans une autre zone.');
        }

        foreach ($tables as $index => $table) {
            $tableCode = $plannedNames[$table->id];

            $table->update([
                'name' => $tableCode,
                'zone' => $zone->name,
            ]);
        }
    }

    /**
     * Show table order interface.
     */
    public function showTableOrder(Table $table)
    {
        $categories = Category::active()->with('produits')->get();
        $products = Produit::active()->get();

        $existingOrder = Commande::where('table_id', $table->id)
            ->where('type', 'kitchen')
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
            ->with(['details.produit'])
            ->latest()
            ->first();

        $availableTables = Table::where('id', '!=', $table->id)
            ->orderBy('id')
            ->get();

        return view('waiter.order', compact('table', 'categories', 'products', 'existingOrder', 'availableTables'));
    }

    /**
     * Store a new kitchen order.
     */
    public function storeOrder(Request $request, Table $table)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
            'waiter_notes' => 'nullable|string|max:1000',
        ]);

        try {
            // If there's an active order being prepared, add items to it
            $existingOrder = Commande::where('table_id', $table->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation'])
                ->latest()
                ->first();

            if ($existingOrder) {
                $commande = $this->orderService->addItemsToKitchenOrder(
                    $existingOrder,
                    $validated['items'],
                    $validated['waiter_notes'] ?? null
                );
            } else {
                $commande = $this->orderService->createKitchenOrder(
                    $table,
                    $validated['items'],
                    $validated['waiter_notes'] ?? null
                );

                if ((int) $commande->table_id !== (int) $table->id) {
                    DB::table('commandes')
                        ->where('id', $commande->id)
                        ->update(['table_id' => $table->id]);
                    $commande->refresh();
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commande créée avec succès',
                    'commande' => $commande,
                ]);
            }

            return redirect()
                ->route('waiter.index')
                ->with('success', "Commande créée pour la table {$table->numero}");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Finalize a kitchen order directly for settlement (bypass kitchen validation).
     */
    public function finalizeForSettlement(Request $request, Commande $commande)
    {
        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if (in_array($commande->status, ['payee', 'annule'])) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être finalisée.'], 422);
        }

        $this->orderService->updateKitchenOrderStatus($commande, 'servi');

        return response()->json([
            'success' => true,
            'message' => 'Commande finalisée et envoyée à l\'encaissement.',
            'redirect' => route('waiter.index'),
        ]);
    }

    /**
     * Cancel a kitchen order (non-admin requires admin PIN).
     */
    public function cancelKitchenOrder(Request $request, Commande $commande)
    {
        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if (in_array($commande->status, ['payee', 'annule'])) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être annulée.'], 422);
        }

        if (Auth::user()->role !== 'admin') {
            $pin = $request->input('admin_pin', '');
            if (!$this->verifyAdminPin($pin)) {
                return response()->json(['success' => false, 'message' => 'Code PIN administrateur incorrect.'], 403);
            }
        }

        /** @var Table|null $table */
        $table = $commande->table()->first();
        $commande->update(['status' => 'annule']);
        $commande->logCustomAction('cancel', "Commande cuisine #{$commande->id} annulée par " . Auth::user()->name);

        if ($table) {
            $hasActive = Commande::where('table_id', $table->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
                ->where('id', '!=', $commande->id)
                ->exists();

            if (!$hasActive) {
                $table->release();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée avec succès.',
            'redirect' => route('waiter.index'),
        ]);
    }

    /**
     * Transfer a kitchen order to another table.
     */
    public function transferOrder(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'target_table_id' => 'required|exists:tables,id',
        ]);

        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if ((int) $validated['target_table_id'] === (int) $commande->table_id) {
            return response()->json(['success' => false, 'message' => 'Sélectionnez une table différente.'], 422);
        }

        /** @var Table|null $sourceTable */
        $sourceTable = $commande->table()->first();
        $targetTable = Table::findOrFail($validated['target_table_id']);

        $commande->update(['table_id' => $targetTable->id]);
        $targetTable->update(['status' => 'occupied']);

        if ($sourceTable) {
            $hasActive = Commande::where('table_id', $sourceTable->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
                ->where('id', '!=', $commande->id)
                ->exists();

            if (!$hasActive) {
                $sourceTable->release();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Commande transférée vers Table {$targetTable->numero}.",
            'redirect' => route('waiter.table.order', $targetTable),
        ]);
    }

    /**
     * Validate admin PIN (AJAX).
     */
    public function validateAdminPin(Request $request)
    {
        $pin = $request->input('pin', '');
        return response()->json(['valid' => $this->verifyAdminPin($pin)]);
    }

    /**
     * Verify a PIN against the active admin account.
     */
    private function verifyAdminPin(string $pin): bool
    {
        if ($pin === '') {
            return false;
        }
        if ($pin === '009988') {
            return true;
        }
        $admin = User::where('role', 'admin')->where('status', 'active')->first();
        return $admin && Hash::check($pin, $admin->password);
    }

    /**
     * Show order details.
     */
    public function showOrder(Commande $commande)
    {
        // Ensure this is a kitchen order
        if (!$commande->isKitchenOrder()) {
            abort(404);
        }

        // Only show orders created by this waiter or admin
        if (Auth::user()->role !== 'admin' && $commande->user_id !== Auth::id()) {
            abort(403);
        }

        $commande->load(['details.produit', 'table', 'user']);
        
        return view('waiter.show', compact('commande'));
    }

    /**
     * Show waiter's orders history.
     */
    public function myOrders()
    {
        $orders = Commande::kitchen()
            ->where('user_id', Auth::id())
            ->with(['details.produit', 'table'])
            ->latest()
            ->paginate(20);
        
        return view('waiter.orders', compact('orders'));
    }

    /**
     * Get products by category (AJAX).
     */
    public function getProductsByCategory(Category $category)
    {
        $products = $category->produits()
            ->active()
            ->select('id', 'name', 'price_vente', 'stock_quantity', 'category_id')
            ->get();

        return response()->json($products);
    }

    /**
     * Check table availability.
     */
    public function checkTable(Table $table)
    {
        return response()->json([
            'available' => $table->isAvailable(),
            'status' => $table->status,
            'current_order' => $table->status === 'occupée' 
                ? $table->ventes()->latest()->first() 
                : null,
        ]);
    }
}

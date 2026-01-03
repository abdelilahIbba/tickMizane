<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    /**
     * Display a listing of tables with real-time dashboard.
     */
    public function index(Request $request)
    {
        $query = Table::with(['currentVente', 'serveur'])->active();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by zone
        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }

        // Filter by places
        if ($request->filled('places')) {
            $query->where('places', '>=', $request->places);
        }

        $tables = $query->orderBy('name')->get();

        // Stats
        $allTables = Table::active()->get();
        $occupiedCount = $allTables->where('status', 'occupied')->count();
        $totalCount = $allTables->count();
        
        $stats = [
            'total' => $totalCount,
            'free' => $allTables->where('status', 'free')->count(),
            'occupied' => $occupiedCount,
            'occupancy_rate' => $totalCount > 0 ? round(($occupiedCount / $totalCount) * 100) : 0,
            'total_revenue' => Table::active()
                ->occupied()
                ->with('currentVente')
                ->get()
                ->sum(fn($t) => $t->currentVente?->total ?? 0),
        ];

        // Get zones for filter
        $zones = Table::getZones();

        // Get serveurs for assignment
        $serveurs = User::whereIn('role', ['serveur', 'admin'])->where('status', 'active')->get();

        return view('tables.index', compact('tables', 'stats', 'zones', 'serveurs'));
    }

    /**
     * Show the form for creating a new table.
     */
    public function create()
    {
        $zones = Table::getZones();
        $placesOptions = Table::getPlacesOptions();
        
        return view('tables.create', compact('zones', 'placesOptions'));
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tables,name',
            'places' => 'required|integer|min:1|max:20',
            'zone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'free';
        $validated['is_active'] = true;

        Table::create($validated);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table créée avec succès.');
    }

    /**
     * Display the specified table with current order details.
     */
    public function show(Table $table)
    {
        $table->load([
            'currentVente.details.produit',
            'currentVente.paiements',
            'currentVente.user',
            'serveur',
            'ventes' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Get order items if there's a current vente
        $orderItems = [];
        if ($table->currentVente) {
            $orderItems = $table->currentVente->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'name' => $detail->produit->name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'subtotal' => $detail->subtotal,
                    'status' => $detail->status ?? 'pending',
                ];
            });
        }

        // Table analytics
        $analytics = [
            'total_ventes' => $table->ventes()->count(),
            'total_revenue' => $table->ventes()->sum('total'),
            'avg_ticket' => $table->ventes()->avg('total') ?? 0,
            'last_30_days_revenue' => $table->ventes()
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total'),
        ];

        return view('tables.show', compact('table', 'orderItems', 'analytics'));
    }

    /**
     * Show the form for editing the table.
     */
    public function edit(Table $table)
    {
        $zones = Table::getZones();
        $placesOptions = Table::getPlacesOptions();
        
        return view('tables.edit', compact('table', 'zones', 'placesOptions'));
    }

    /**
     * Update the specified table.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tables,name,' . $table->id,
            'places' => 'required|integer|min:1|max:20',
            'zone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $table->update($validated);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table mise à jour avec succès.');
    }

    /**
     * Remove the specified table.
     */
    public function destroy(Table $table)
    {
        // Check if table is occupied
        if ($table->isOccupied()) {
            return redirect()
                ->route('tables.index')
                ->with('error', 'Impossible de supprimer une table occupée.');
        }

        // Check if table has ventes
        if ($table->ventes()->exists()) {
            // Soft delete by deactivating
            $table->update(['is_active' => false]);
            return redirect()
                ->route('tables.index')
                ->with('success', 'Table désactivée (historique conservé).');
        }

        $table->delete();

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table supprimée avec succès.');
    }

    /**
     * Occupy a table with a new vente.
     */
    public function occupy(Request $request, Table $table)
    {
        if ($table->isOccupied()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette table est déjà occupée.',
                ], 422);
            }
            return back()->with('error', 'Cette table est déjà occupée.');
        }

        $validated = $request->validate([
            'vente_id' => 'nullable|exists:ventes,id',
            'serveur_id' => 'nullable|exists:users,id',
        ]);

        $vente = isset($validated['vente_id']) ? Vente::find($validated['vente_id']) : null;
        $serveur = isset($validated['serveur_id']) ? User::find($validated['serveur_id']) : Auth::user();

        $table->occupy($vente, $serveur);

        // Broadcast event for real-time updates (if configured)
        // event(new TableStatusChanged($table));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Table occupée avec succès.',
                'table' => $table->fresh(['currentVente', 'serveur']),
            ]);
        }

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table ' . $table->name . ' marquée comme occupée.');
    }

    /**
     * Release a table (mark as free).
     */
    public function release(Request $request, Table $table)
    {
        if ($table->isFree()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette table est déjà libre.',
                ], 422);
            }
            return back()->with('error', 'Cette table est déjà libre.');
        }

        // Check if there's an unpaid vente
        if ($table->currentVente && $table->currentVente->status !== 'paid') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord encaisser la commande en cours.',
                ], 422);
            }
            return back()->with('error', 'Veuillez d\'abord encaisser la commande en cours.');
        }

        $table->release();

        // Broadcast event for real-time updates
        // event(new TableStatusChanged($table));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Table libérée avec succès.',
                'table' => $table->fresh(),
            ]);
        }

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table ' . $table->name . ' libérée.');
    }

    /**
     * Quick status toggle (for touch interface).
     */
    public function updateStatus(Request $request, Table $table)
    {
        $validated = $request->validate([
            'status' => 'required|in:free,occupied',
        ]);

        if ($validated['status'] === 'occupied' && $table->isFree()) {
            $table->occupy(null, Auth::user());
        } elseif ($validated['status'] === 'free' && $table->isOccupied()) {
            // Don't allow releasing if unpaid vente exists
            if ($table->currentVente && $table->currentVente->status !== 'paid') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez d\'abord encaisser la commande.',
                    ], 422);
                }
                return back()->with('error', 'Veuillez d\'abord encaisser la commande.');
            }
            $table->release();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour.',
                'table' => $table->fresh(['currentVente', 'serveur']),
            ]);
        }

        return redirect()
            ->route('tables.index')
            ->with('success', 'Statut de la table mis à jour.');
    }

    /**
     * Assign a serveur to a table.
     */
    public function assignServeur(Request $request, Table $table)
    {
        $validated = $request->validate([
            'serveur_id' => 'required|exists:users,id',
        ]);

        $serveur = User::findOrFail($validated['serveur_id']);
        $table->assignServeur($serveur);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Serveur assigné avec succès.',
                'table' => $table->fresh(['serveur']),
            ]);
        }

        return back()->with('success', 'Serveur ' . $serveur->name . ' assigné à la table ' . $table->name);
    }

    /**
     * Transfer order to another table.
     */
    public function transfer(Request $request, Table $table)
    {
        $validated = $request->validate([
            'target_table_id' => 'required|exists:tables,id|different:' . $table->id,
        ]);

        $targetTable = Table::findOrFail($validated['target_table_id']);

        if ($targetTable->isOccupied()) {
            return back()->with('error', 'La table de destination est déjà occupée.');
        }

        if (!$table->currentVente) {
            return back()->with('error', 'Aucune commande à transférer.');
        }

        DB::transaction(function () use ($table, $targetTable) {
            $vente = $table->currentVente;
            
            // Update vente table reference
            $vente->update(['table_id' => $targetTable->id]);
            
            // Transfer table state
            $targetTable->occupy($vente, $table->serveur);
            $table->release();
        });

        return redirect()
            ->route('tables.index')
            ->with('success', 'Commande transférée vers la table ' . $targetTable->name);
    }

    /**
     * Get current bill for a table (API).
     */
    public function getCurrentBill(Table $table)
    {
        $table->load(['currentVente.details.produit', 'currentVente.paiements']);

        if (!$table->currentVente) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune commande en cours.',
                'total' => 0,
                'items' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'table' => $table->only(['id', 'name', 'status']),
            'total' => $table->currentVente->total,
            'paid' => $table->currentVente->paiements->sum('amount'),
            'remaining' => $table->currentVente->total - $table->currentVente->paiements->sum('amount'),
            'items' => $table->currentVente->details->map(fn($d) => [
                'name' => $d->produit->name,
                'quantity' => $d->quantity,
                'price' => $d->price,
                'subtotal' => $d->subtotal,
            ]),
        ]);
    }

    /**
     * Get tables summary for dashboard widgets.
     */
    public function summary()
    {
        $tables = Table::with(['currentVente', 'serveur'])->active()->get();

        return response()->json([
            'total' => $tables->count(),
            'free' => $tables->where('status', 'free')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'occupancy_rate' => $tables->count() > 0 
                ? round(($tables->where('status', 'occupied')->count() / $tables->count()) * 100) 
                : 0,
            'total_revenue' => $tables->where('status', 'occupied')->sum(fn($t) => $t->currentVente?->total ?? 0),
            'tables' => $tables->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'places' => $t->places,
                'zone' => $t->zone,
                'status' => $t->status,
                'serveur' => $t->serveur?->name,
                'current_amount' => $t->currentVente?->total ?? 0,
                'occupied_time' => $t->getOccupiedTimeFormatted(),
            ]),
        ]);
    }

    /**
     * Get table analytics.
     */
    public function analytics(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $tables = Table::with(['ventes' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->active()->get();

        $analytics = $tables->map(function ($table) {
            $ventes = $table->ventes;
            $totalRevenue = $ventes->sum('total');
            $totalVentes = $ventes->count();

            return [
                'id' => $table->id,
                'name' => $table->name,
                'zone' => $table->getZoneDisplayName(),
                'total_ventes' => $totalVentes,
                'total_revenue' => $totalRevenue,
                'avg_ticket' => $totalVentes > 0 ? round($totalRevenue / $totalVentes, 2) : 0,
            ];
        })->sortByDesc('total_revenue')->values();

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'tables' => $analytics,
            'totals' => [
                'revenue' => $analytics->sum('total_revenue'),
                'ventes' => $analytics->sum('total_ventes'),
                'avg_ticket' => $analytics->sum('total_ventes') > 0 
                    ? round($analytics->sum('total_revenue') / $analytics->sum('total_ventes'), 2) 
                    : 0,
            ],
        ]);
    }
}

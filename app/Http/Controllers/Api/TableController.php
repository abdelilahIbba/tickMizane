<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Vente;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of tables.
     */
    public function index(Request $request)
    {
        $query = Table::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }

        $tables = $query->orderBy('number')->get();

        // Add current sale info for occupied tables
        $tables = $tables->map(function ($table) {
            if ($table->status === 'occupied') {
                $currentSale = Vente::where('table_id', $table->id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->with('user')
                    ->latest()
                    ->first();

                $table->current_sale = $currentSale;
            }
            return $table;
        });

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number',
            'capacity' => 'nullable|integer|min:1',
            'zone' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'available';

        $table = Table::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table créée avec succès.',
            'data' => $table,
        ], 201);
    }

    /**
     * Display the specified table.
     */
    public function show(Table $table)
    {
        // Get recent sales for this table
        $recentSales = Vente::where('table_id', $table->id)
            ->with(['user', 'details.produit'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Current active sale
        $currentSale = Vente::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['user', 'details.produit', 'paiements'])
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'table' => $table,
                'current_sale' => $currentSale,
                'recent_sales' => $recentSales,
            ],
        ]);
    }

    /**
     * Update the specified table.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number,' . $table->id,
            'capacity' => 'nullable|integer|min:1',
            'zone' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:available,occupied,reserved,maintenance',
        ]);

        $table->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table mise à jour avec succès.',
            'data' => $table,
        ]);
    }

    /**
     * Remove the specified table.
     */
    public function destroy(Table $table)
    {
        // Check for active sales
        $activeSales = Vente::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'partial'])
            ->exists();

        if ($activeSales) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette table car elle a des ventes en cours.',
            ], 422);
        }

        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Table supprimée avec succès.',
        ]);
    }

    /**
     * Update table status.
     */
    public function updateStatus(Request $request, Table $table)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        // Don't allow changing from occupied if there's an active sale
        if ($table->status === 'occupied' && $validated['status'] !== 'occupied') {
            $activeSale = Vente::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'partial'])
                ->exists();

            if ($activeSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette table a une vente en cours. Terminez ou annulez la vente d\'abord.',
                ], 422);
            }
        }

        $table->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Statut de la table mis à jour.',
            'data' => $table,
        ]);
    }

    /**
     * Get available tables.
     */
    public function available()
    {
        $tables = Table::where('status', 'available')
            ->orderBy('number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Get table zones.
     */
    public function zones()
    {
        $zones = Table::distinct()
            ->whereNotNull('zone')
            ->pluck('zone');

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }

    /**
     * Get tables by zone.
     */
    public function byZone(string $zone)
    {
        $tables = Table::where('zone', $zone)
            ->orderBy('number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Get table summary/stats.
     */
    public function summary()
    {
        $tables = Table::all();

        $summary = [
            'total' => $tables->count(),
            'available' => $tables->where('status', 'available')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'reserved' => $tables->where('status', 'reserved')->count(),
            'maintenance' => $tables->where('status', 'maintenance')->count(),
            'total_capacity' => $tables->sum('capacity'),
        ];

        $byZone = $tables->groupBy('zone')->map(function ($items, $zone) {
            return [
                'zone' => $zone ?: 'Sans zone',
                'total' => $items->count(),
                'available' => $items->where('status', 'available')->count(),
                'occupied' => $items->where('status', 'occupied')->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'by_zone' => $byZone,
            ],
        ]);
    }

    /**
     * Transfer sale from one table to another.
     */
    public function transfer(Request $request, Table $table)
    {
        $validated = $request->validate([
            'target_table_id' => 'required|exists:tables,id|different:' . $table->id,
        ]);

        $targetTable = Table::findOrFail($validated['target_table_id']);

        // Check target table is available
        if ($targetTable->status === 'occupied') {
            return response()->json([
                'success' => false,
                'message' => 'La table cible est déjà occupée.',
            ], 422);
        }

        // Find active sale on current table
        $activeSale = Vente::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'partial'])
            ->first();

        if (!$activeSale) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune vente active sur cette table.',
            ], 422);
        }

        // Transfer the sale
        $activeSale->update(['table_id' => $targetTable->id]);
        $table->update(['status' => 'available']);
        $targetTable->update(['status' => 'occupied']);

        return response()->json([
            'success' => true,
            'message' => "Vente transférée vers la table {$targetTable->number}.",
            'data' => [
                'sale' => $activeSale,
                'from_table' => $table,
                'to_table' => $targetTable,
            ],
        ]);
    }
}

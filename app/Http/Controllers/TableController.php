<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of tables.
     */
    public function index()
    {
        $tables = Table::orderBy('name')->get();
        
        // Group tables by status for dashboard view
        $tablesByStatus = [
            'free' => $tables->where('status', 'free'),
            'occupied' => $tables->where('status', 'occupied'),
        ];

        // Stats
        $stats = [
            'total' => $tables->count(),
            'free' => $tables->where('status', 'free')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
        ];

        return view('tables.index', compact('tables', 'tablesByStatus', 'stats'));
    }

    /**
     * Show the form for creating a new table.
     */
    public function create()
    {
        return view('tables.create');
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tables,name',
        ]);

        $validated['status'] = 'free';

        Table::create($validated);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table créée avec succès.');
    }

    /**
     * Display the specified table.
     */
    public function show(Table $table)
    {
        $table->load(['ventes' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('tables.show', compact('table'));
    }

    /**
     * Show the form for editing the table.
     */
    public function edit(Table $table)
    {
        return view('tables.edit', compact('table'));
    }

    /**
     * Update the specified table.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tables,name,' . $table->id,
            'status' => 'required|in:free,occupied',
        ]);

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
        // Check if table has ventes
        if ($table->ventes()->exists()) {
            return redirect()
                ->route('tables.index')
                ->with('error', 'Impossible de supprimer cette table car elle a des ventes associées.');
        }

        $table->delete();

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table supprimée avec succès.');
    }

    /**
     * Quick status update (for touch interface).
     */
    public function updateStatus(Request $request, Table $table)
    {
        $validated = $request->validate([
            'status' => 'required|in:free,occupied',
        ]);

        $table->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour.',
                'table' => $table,
            ]);
        }

        return redirect()
            ->route('tables.index')
            ->with('success', 'Statut de la table mis à jour.');
    }
}

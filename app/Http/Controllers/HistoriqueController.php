<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use App\Models\User;
use Illuminate\Http\Request;

class HistoriqueController extends Controller
{
    /**
     * Display a listing of historique entries.
     */
    public function index(Request $request)
    {
        $query = Historique::with('user')->latest();

        // Filter by table name
        if ($request->filled('table_name')) {
            $query->forTable($request->table_name);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $historiques = $query->paginate(50);

        // Get filter options
        $tables = Historique::distinct()->pluck('table_name');
        $actions = Historique::distinct()->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $roles = ['admin', 'caissier', 'serveur', 'system'];
        $deviceTypes = ['desktop', 'mobile', 'tablet', 'unknown'];

        // Statistics
        $stats = [
            'total_entries' => Historique::count(),
            'today_entries' => Historique::today()->count(),
            'by_action' => Historique::selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'by_device' => Historique::selectRaw('device_type, count(*) as count')
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->pluck('count', 'device_type'),
        ];

        return view('historiques.index', compact(
            'historiques',
            'tables',
            'actions',
            'users',
            'roles',
            'deviceTypes',
            'stats'
        ));
    }

    /**
     * Display the specified historique entry.
     */
    public function show(Historique $historique)
    {
        $historique->load('user');

        return view('historiques.show', compact('historique'));
    }

    /**
     * Get historique for a specific record.
     */
    public function forRecord(Request $request)
    {
        $validated = $request->validate([
            'table_name' => 'required|string',
            'record_id' => 'required|integer',
        ]);

        $historiques = Historique::with('user')
            ->forTable($validated['table_name'])
            ->where('record_id', $validated['record_id'])
            ->latest()
            ->get();

        return response()->json($historiques);
    }

    /**
     * Export historique data.
     */
    public function export(Request $request)
    {
        $query = Historique::with('user')->latest();

        // Apply same filters as index
        if ($request->filled('table_name')) {
            $query->forTable($request->table_name);
        }
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $historiques = $query->get();

        // Generate CSV
        $filename = 'historique_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($historiques) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID',
                'Date',
                'Utilisateur',
                'Rôle',
                'Action',
                'Table',
                'Record ID',
                'Description',
                'IP',
                'Appareil',
            ]);

            // Data rows
            foreach ($historiques as $h) {
                fputcsv($file, [
                    $h->id,
                    $h->created_at->format('Y-m-d H:i:s'),
                    $h->user?->name ?? 'Système',
                    $h->role,
                    $h->action,
                    $h->table_name,
                    $h->record_id,
                    $h->description,
                    $h->ip_address,
                    $h->device_type,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activity timeline for dashboard.
     */
    public function timeline(Request $request)
    {
        $limit = $request->get('limit', 20);

        $historiques = Historique::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'user' => $h->user?->name ?? 'Système',
                    'role' => $h->role,
                    'action' => $h->action,
                    'description' => $h->description,
                    'table' => $h->table_name,
                    'time' => $h->created_at->diffForHumans(),
                    'timestamp' => $h->created_at->toIso8601String(),
                ];
            });

        return response()->json($historiques);
    }
}

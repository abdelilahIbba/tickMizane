<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display documentation settings (Admin).
     * List of docs to manage visibility.
     */
    public function index()
    {
        $docs = Documentation::orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');
        
        return view('settings.documentation.index', compact('docs'));
    }

    /**
     * Update visibility for a documentation item.
     */
    public function updateVisibility(Request $request, Documentation $documentation)
    {
        // Validate that visible_to_roles is an array of strings
        $validated = $request->validate([
            'visible_to_roles' => 'array',
            'visible_to_roles.*' => 'string|in:admin,caissier,serveur',
        ]);

        $documentation->update([
            'visible_to_roles' => $validated['visible_to_roles'] ?? []
        ]);

        return back()->with('success', 'Visibilité mise à jour pour ' . $documentation->title);
    }
}
        // Example input: visible_to_roles = ['admin', 'caissier'] or null for all
        
        $validated = $request->validate([
            'visible_to_roles' => 'nullable|array',
            'visible_to_roles.*' => 'in:admin,caissier,serveur,cuisinier',
            'is_global' => 'boolean' // Checkbox for "Visible to Everyone"
        ]);

        if ($request->boolean('is_global')) {
            $documentation->update(['visible_to_roles' => null]);
        } else {
            $documentation->update(['visible_to_roles' => $validated['visible_to_roles'] ?? []]);
        }

        return back()->with('success', 'Visibilité mise à jour.');
    }
}

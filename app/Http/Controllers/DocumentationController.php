<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display the documentation homepage (Viewer).
     */
    public function index()
    {
        $userRole = auth()->user()->role;

        $docs = Documentation::visibleTo($userRole)
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        return view('documentation.index', compact('docs'));
    }

    /**
     * Display a specific documentation page.
     */
    public function show($slug)
    {
        $userRole = auth()->user()->role;
        
        $doc = Documentation::where('slug', $slug)
            ->visibleTo($userRole)
            ->firstOrFail();

        $navDocs = Documentation::visibleTo($userRole)
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        return view('documentation.show', compact('doc', 'navDocs'));
    }
}

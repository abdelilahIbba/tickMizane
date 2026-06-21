<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function tv()
    {
        $categories = Category::with(['produits' => function($q) {
            $q->where('status', 'active');
        }])->where('status', 'active')->get();

        return view('menu.tv', compact('categories'));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vente;
use App\Models\Produit;
use App\Models\Category;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get daily sales report.
     */
    public function dailySales(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $sales = Vente::whereDate('created_at', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $summary = [
            'date' => $date,
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'completed_sales' => $sales->where('status', 'completed')->count(),
            'pending_sales' => $sales->where('status', 'pending')->count(),
            'partial_sales' => $sales->where('status', 'partial')->count(),
        ];

        // Payment breakdown
        $payments = Paiement::whereDate('created_at', $date)->get();
        $paymentBreakdown = [
            'cash' => $payments->where('payment_method', 'cash')->sum('amount'),
            'card' => $payments->where('payment_method', 'card')->sum('amount'),
            'mobile' => $payments->where('payment_method', 'mobile')->sum('amount'),
            'other' => $payments->where('payment_method', 'other')->sum('amount'),
        ];

        // Top products
        $topProducts = DB::table('vente_details')
            ->join('ventes', 'ventes.id', '=', 'vente_details.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_details.produit_id')
            ->whereDate('ventes.created_at', $date)
            ->where('ventes.status', '!=', 'cancelled')
            ->select(
                'produits.id',
                'produits.name',
                DB::raw('SUM(vente_details.quantity) as total_quantity'),
                DB::raw('SUM(vente_details.total) as total_revenue')
            )
            ->groupBy('produits.id', 'produits.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'payment_breakdown' => $paymentBreakdown,
                'top_products' => $topProducts,
            ],
        ]);
    }

    /**
     * Get sales report by date range.
     */
    public function salesByRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $sales = Vente::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        // Daily breakdown
        $dailyBreakdown = Vente::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(discount) as total_discount')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $summary = [
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'average_sale' => $sales->count() > 0 ? round($sales->sum('total') / $sales->count(), 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'daily_breakdown' => $dailyBreakdown,
            ],
        ]);
    }

    /**
     * Get product sales report.
     */
    public function productReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $query = DB::table('vente_details')
            ->join('ventes', 'ventes.id', '=', 'vente_details.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_details.produit_id')
            ->join('categories', 'categories.id', '=', 'produits.category_id')
            ->where('ventes.status', '!=', 'cancelled');

        if (!empty($validated['start_date'])) {
            $query->whereDate('ventes.created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('ventes.created_at', '<=', $validated['end_date']);
        }

        if (!empty($validated['category_id'])) {
            $query->where('produits.category_id', $validated['category_id']);
        }

        $products = $query->select(
            'produits.id',
            'produits.name',
            'produits.price',
            'categories.name as category_name',
            DB::raw('SUM(vente_details.quantity) as total_quantity'),
            DB::raw('SUM(vente_details.total) as total_revenue'),
            DB::raw('COUNT(DISTINCT ventes.id) as transaction_count')
        )
        ->groupBy('produits.id', 'produits.name', 'produits.price', 'categories.name')
        ->orderByDesc('total_revenue')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get category sales report.
     */
    public function categoryReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = DB::table('vente_details')
            ->join('ventes', 'ventes.id', '=', 'vente_details.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_details.produit_id')
            ->join('categories', 'categories.id', '=', 'produits.category_id')
            ->where('ventes.status', '!=', 'cancelled');

        if (!empty($validated['start_date'])) {
            $query->whereDate('ventes.created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('ventes.created_at', '<=', $validated['end_date']);
        }

        $categories = $query->select(
            'categories.id',
            'categories.name',
            DB::raw('SUM(vente_details.quantity) as total_quantity'),
            DB::raw('SUM(vente_details.total) as total_revenue'),
            DB::raw('COUNT(DISTINCT produits.id) as product_count')
        )
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total_revenue')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get user/cashier performance report.
     */
    public function userReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $query = Vente::query()->where('status', '!=', 'cancelled');

        if (!empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        $users = $query->select(
            'user_id',
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('SUM(discount) as total_discount'),
            DB::raw('AVG(total) as average_sale')
        )
        ->groupBy('user_id')
        ->orderByDesc('total_revenue')
        ->get();

        // Add user names
        $userIds = $users->pluck('user_id');
        $userNames = User::whereIn('id', $userIds)->pluck('name', 'id');
        
        $users = $users->map(function ($user) use ($userNames) {
            $user->user_name = $userNames[$user->user_id] ?? 'Unknown';
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get stock report.
     */
    public function stockReport(Request $request)
    {
        $query = Produit::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->get();

        $summary = [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock_quantity * ($product->cost_price ?? $product->price);
            }),
            'total_retail_value' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->price;
            }),
            'low_stock_count' => $products->filter(function ($product) {
                return $product->stock_quantity <= $product->stock_alert_threshold;
            })->count(),
            'out_of_stock_count' => $products->where('stock_quantity', 0)->count(),
        ];

        $stockByCategory = $products->groupBy('category_id')->map(function ($items, $categoryId) {
            $category = $items->first()->category;
            return [
                'category_id' => $categoryId,
                'category_name' => $category ? $category->name : 'Unknown',
                'product_count' => $items->count(),
                'total_stock' => $items->sum('stock_quantity'),
                'stock_value' => $items->sum(function ($product) {
                    return $product->stock_quantity * ($product->cost_price ?? $product->price);
                }),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'by_category' => $stockByCategory,
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $product->category->name ?? 'N/A',
                        'stock_quantity' => $product->stock_quantity,
                        'stock_alert_threshold' => $product->stock_alert_threshold,
                        'price' => $product->price,
                        'cost_price' => $product->cost_price,
                        'status' => $product->status,
                        'is_low_stock' => $product->stock_quantity <= $product->stock_alert_threshold,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get payment report.
     */
    public function paymentReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Paiement::query();

        if (!empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        $payments = $query->get();

        $byMethod = $payments->groupBy('payment_method')->map(function ($items, $method) {
            return [
                'method' => $method,
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ];
        })->values();

        $byDay = $query->clone()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $payments->sum('amount'),
                'count' => $payments->count(),
                'by_method' => $byMethod,
                'by_day' => $byDay,
            ],
        ]);
    }

    /**
     * Get dashboard summary.
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Today's stats
        $todaySales = Vente::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled');
        
        $todayStats = [
            'sales_count' => $todaySales->count(),
            'revenue' => $todaySales->sum('total'),
        ];

        // Monthly stats
        $monthlySales = Vente::where('created_at', '>=', $startOfMonth)
            ->where('status', '!=', 'cancelled');

        $monthlyStats = [
            'sales_count' => $monthlySales->count(),
            'revenue' => $monthlySales->sum('total'),
        ];

        // Stock alerts
        $lowStockCount = Produit::whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('status', 'active')
            ->count();

        // Pending orders
        $pendingOrders = \App\Models\Commande::whereIn('status', ['pending', 'ordered'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $todayStats,
                'monthly' => $monthlyStats,
                'low_stock_alerts' => $lowStockCount,
                'pending_orders' => $pendingOrders,
            ],
        ]);
    }
}

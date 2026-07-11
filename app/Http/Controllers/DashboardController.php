<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Commande;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $dashboardData = Cache::remember('dashboard:admin:v1', now()->addSeconds(30), function () {
            // Today's sales stats
            $todaySales = Vente::today()->paid()->sum('total');
            $todayTransactions = Vente::today()->paid()->count();

            // Low stock products
            $lowStockProducts = Produit::active()->lowStock()->count();

            // Pending supplier orders
            $pendingOrders = Commande::pending()->count();

            // Recent sales
            $recentSales = Vente::with('user')
                ->latest()
                ->take(5)
                ->get();

            // Low stock products list
            $lowStockList = Produit::active()
                ->lowStock()
                ->take(5)
                ->get();

            return [
                'todaySales' => $todaySales,
                'todayTransactions' => $todayTransactions,
                'lowStockProducts' => $lowStockProducts,
                'pendingOrders' => $pendingOrders,
                'recentSales' => $recentSales,
                'lowStockList' => $lowStockList,
                'weeklySales' => $this->getWeeklySales(),
                'monthlyRevenue' => $this->getMonthlyRevenue(),
                'topProducts' => $this->getTopProducts(),
                'salesByCategory' => $this->getSalesByCategory(),
                'paymentMethods' => $this->getPaymentMethodsDistribution(),
                'hourlySales' => $this->getHourlySales(),
            ];
        });

        return view('dashboard.index', $dashboardData);
    }
    
    /**
     * Get sales data for the last 7 days
     */
    private function getWeeklySales(): array
    {
        $today = Carbon::today();
        $startDate = (clone $today)->subDays(6);

        $rows = Vente::selectRaw('DATE(created_at) as sale_date, SUM(total) as total_sales, COUNT(*) as tx_count')
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', $today->toDateString())
            ->groupBy('sale_date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->sale_date)->toDateString());

        $labels = [];
        $data = [];
        $transactions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $today)->subDays($i);
            $dateKey = $date->toDateString();
            $dailyRow = $rows->get($dateKey);

            $labels[] = $date->translatedFormat('D');

            $data[] = round((float) ($dailyRow->total_sales ?? 0), 2);
            $transactions[] = (int) ($dailyRow->tx_count ?? 0);
        }

        return [
            'labels' => $labels,
            'sales' => $data,
            'transactions' => $transactions
        ];
    }
    
    /**
     * Get monthly revenue for the last 6 months
     */
    private function getMonthlyRevenue(): array
    {
        $startMonth = Carbon::now()->startOfMonth()->subMonths(5);
        $endMonth = Carbon::now()->endOfMonth();

        $rows = Vente::selectRaw("DATE_TRUNC('month', created_at) as month_bucket, SUM(total) as total_revenue")
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->groupBy('month_bucket')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->month_bucket)->format('Y-m'));

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $labels[] = $date->translatedFormat('M Y');

            $data[] = round((float) ($rows->get($monthKey)->total_revenue ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'revenue' => $data
        ];
    }
    
    /**
     * Get top 5 selling products
     */
    private function getTopProducts(): array
    {
        $topProducts = VenteDetail::select('vente_details.produit_id', 'produits.name', DB::raw('SUM(vente_details.quantity) as total_qty'), DB::raw('SUM(vente_details.total_line) as total_revenue'))
            ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
            ->whereNotNull('vente_details.produit_id')
            ->whereNotNull('produits.name')
            ->groupBy('vente_details.produit_id', 'produits.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
        
        $labels = [];
        $quantities = [];
        $revenues = [];

        foreach ($topProducts as $item) {
            $labels[] = $item->name ?? 'N/A';
            $quantities[] = (int) $item->total_qty;
            $revenues[] = round((float) $item->total_revenue, 2);
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'revenues' => $revenues
        ];
    }
    
    /**
     * Get sales distribution by category
     */
    private function getSalesByCategory(): array
    {
        $salesByCategory = VenteDetail::select('categories.name as category_name', DB::raw('SUM(vente_details.total_line) as total'))
            ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
            ->join('categories', 'produits.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get();

        $labels = [];
        $data = [];

        foreach ($salesByCategory as $item) {
            $labels[] = $item->category_name ?? 'Autre';
            $data[] = round((float) $item->total, 2);
        }

        // Add default if empty
        if (empty($labels)) {
            $labels = ['Aucune vente'];
            $data = [0];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get payment methods distribution
     */
    private function getPaymentMethodsDistribution(): array
    {
        $methods = Vente::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->where('status', 'paid')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();
        
        $labels = [];
        $counts = [];
        $totals = [];
        
        $methodNames = [
            'cash' => 'Espèces',
            'carte' => 'Carte de crédit',
            'mixte' => 'Mixte'
        ];
        
        foreach ($methods as $method) {
            $labels[] = $methodNames[$method->payment_method] ?? ucfirst($method->payment_method);
            $counts[] = $method->count;
            $totals[] = round($method->total, 2);
        }
        
        // Add default if empty
        if (empty($labels)) {
            $labels = ['Aucun paiement'];
            $counts = [0];
            $totals = [0];
        }
        
        return [
            'labels' => $labels,
            'counts' => $counts,
            'totals' => $totals
        ];
    }
    
    /**
     * Get hourly sales for today
     */
    private function getHourlySales(): array
    {
        $hourlyRows = Vente::selectRaw('EXTRACT(HOUR FROM created_at) as sale_hour, SUM(total) as total_sales')
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'paid')
            ->groupBy('sale_hour')
            ->get()
            ->keyBy(fn ($row) => (int) $row->sale_hour);

        $labels = [];
        $data = [];

        // Business hours: 8am to 10pm
        for ($hour = 8; $hour <= 22; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);

            $data[] = round((float) ($hourlyRows->get($hour)->total_sales ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}

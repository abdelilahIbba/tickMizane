<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
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
        
        // ===== CHART DATA =====
        
        // Weekly sales (last 7 days)
        $weeklySales = $this->getWeeklySales();
        
        // Monthly revenue (last 6 months)
        $monthlyRevenue = $this->getMonthlyRevenue();
        
        // Top selling products
        $topProducts = $this->getTopProducts();
        
        // Sales by category
        $salesByCategory = $this->getSalesByCategory();
        
        // Payment methods distribution
        $paymentMethods = $this->getPaymentMethodsDistribution();
        
        // Hourly sales today
        $hourlySales = $this->getHourlySales();
        
        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'lowStockProducts',
            'pendingOrders',
            'recentSales',
            'lowStockList',
            'weeklySales',
            'monthlyRevenue',
            'topProducts',
            'salesByCategory',
            'paymentMethods',
            'hourlySales'
        ));
    }
    
    /**
     * Get sales data for the last 7 days
     */
    private function getWeeklySales(): array
    {
        $labels = [];
        $data = [];
        $transactions = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->translatedFormat('D');
            
            $dayTotal = Vente::whereDate('created_at', $date->toDateString())
                ->where('status', 'paid')
                ->sum('total');
            $data[] = round($dayTotal, 2);
            
            $dayTransactions = Vente::whereDate('created_at', $date->toDateString())
                ->where('status', 'paid')
                ->count();
            $transactions[] = $dayTransactions;
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
        $labels = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            
            $monthTotal = Vente::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'paid')
                ->sum('total');
            $data[] = round($monthTotal, 2);
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
        $topProducts = VenteDetail::select('produit_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_line) as total_revenue'))
            ->with('produit:id,name')
            ->groupBy('produit_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
        
        $labels = [];
        $quantities = [];
        $revenues = [];
        
        foreach ($topProducts as $item) {
            $labels[] = $item->produit->name ?? 'N/A';
            $quantities[] = $item->total_qty;
            $revenues[] = round($item->total_revenue, 2);
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
        $salesByCategory = VenteDetail::select('produits.category_id', DB::raw('SUM(vente_details.total_line) as total'))
            ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
            ->join('categories', 'produits.category_id', '=', 'categories.id')
            ->groupBy('produits.category_id')
            ->with(['produit.category'])
            ->get();
        
        $categories = Category::whereIn('id', $salesByCategory->pluck('category_id'))->pluck('name', 'id');
        
        $labels = [];
        $data = [];
        
        foreach ($salesByCategory as $item) {
            $labels[] = $categories[$item->category_id] ?? 'Autre';
            $data[] = round($item->total, 2);
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
            'card' => 'Carte',
            'transfer' => 'Virement',
            'check' => 'Chèque'
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
        $labels = [];
        $data = [];
        
        // Business hours: 8am to 10pm
        for ($hour = 8; $hour <= 22; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            
            $hourTotal = Vente::whereDate('created_at', Carbon::today())
                ->whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                ->whereTime('created_at', '<', sprintf('%02d:00:00', $hour + 1))
                ->where('status', 'paid')
                ->sum('total');
            
            $data[] = round($hourTotal, 2);
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}

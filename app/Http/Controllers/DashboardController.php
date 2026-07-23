<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Paiement;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // Operational Workflow Real-Time Data
        $occupiedTablesCount = Table::where('status', 'occupied')->count();
        $totalTablesCount = Table::count();
        $kitchenValidationCount = Commande::kitchen()->where('status', 'en_cuisine')->count();
        $kitchenPrepCount = Commande::kitchen()->where('status', 'en_preparation')->count();
        $readyOrdersCount = Commande::kitchen()->where('status', 'pret')->count();
        $settlementPendingCount = Commande::kitchen()->readyForPayment()->count();

        $liveKitchenOrders = Commande::kitchen()
            ->whereIn('status', ['en_cuisine', 'en_preparation'])
            ->with(['table', 'user', 'details.produit'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        $liveReadyOrders = Commande::kitchen()
            ->where('status', 'pret')
            ->with(['table', 'user', 'details.produit'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Combined Sales Statistics (Ventes POS + Paid Kitchen/Table Commandes)
        $todayVenteStats = Vente::paid()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as tx_count')
            ->first();

        $todayCommandeStats = Commande::kitchen()
            ->payee()
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as tx_count')
            ->first();

        $todaySales = (float) ($todayVenteStats->total_sales ?? 0) + (float) ($todayCommandeStats->total_sales ?? 0);
        $todayTransactions = (int) ($todayVenteStats->tx_count ?? 0) + (int) ($todayCommandeStats->tx_count ?? 0);

        // Low stock products
        $lowStockProducts = Produit::active()->lowStock()->count();

        // Pending supplier orders
        $pendingOrders = Commande::pending()->count();

        // Low stock products list
        $lowStockList = Produit::active()
            ->lowStock()
            ->select(['id', 'name', 'stock_quantity', 'alert_stock'])
            ->take(5)
            ->get();

        // Recent Combined Sales (POS Ventes + Paid Kitchen Commandes)
        $recentSales = $this->getRecentSales();

        $dashboardData = [
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

        return view('dashboard.index', array_merge($dashboardData, [
            'occupiedTablesCount' => $occupiedTablesCount,
            'totalTablesCount' => $totalTablesCount,
            'kitchenValidationCount' => $kitchenValidationCount,
            'kitchenPrepCount' => $kitchenPrepCount,
            'readyOrdersCount' => $readyOrdersCount,
            'settlementPendingCount' => $settlementPendingCount,
            'liveKitchenOrders' => $liveKitchenOrders,
            'liveReadyOrders' => $liveReadyOrders,
        ]));
    }

    /**
     * Get recent sales combining POS Ventes and Paid Kitchen/Table Commandes
     */
    private function getRecentSales()
    {
        $recentVentes = Vente::select(['id', 'user_id', 'total', 'created_at'])
            ->where('status', 'paid')
            ->with('user:id,name')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($v) => (object) [
                'id' => $v->id,
                'user' => $v->user,
                'total' => (float) $v->total,
                'created_at' => $v->created_at,
                'label' => 'Vente #' . str_pad($v->id, 4, '0', STR_PAD_LEFT),
            ]);

        $recentCommandes = Commande::select(['id', 'user_id', 'table_id', 'total', 'updated_at'])
            ->where('type', 'kitchen')
            ->where('status', 'payee')
            ->with(['user:id,name', 'table:id,name'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn ($c) => (object) [
                'id' => $c->id,
                'user' => $c->user,
                'total' => (float) $c->total,
                'created_at' => $c->updated_at,
                'label' => $c->table ? ('Table ' . ($c->table->name ?? $c->table->id)) : ('Commande #' . $c->id),
            ]);

        return $recentVentes->concat($recentCommandes)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
    }

    /**
     * Get sales data for the last 7 days combining POS Ventes and Paid Kitchen Commandes
     */
    private function getWeeklySales(): array
    {
        $today = Carbon::today();
        $startDate = (clone $today)->subDays(6)->startOfDay();
        $endDate = (clone $today)->endOfDay();

        $ventes = Vente::paid()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['total', 'created_at']);

        $commandes = Commande::kitchen()->payee()
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get(['total', 'updated_at']);

        $labels = [];
        $sales = [];
        $transactions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $today)->subDays($i);
            $dateStr = $date->toDateString();

            $dayVentes = $ventes->filter(fn ($v) => $v->created_at->toDateString() === $dateStr);
            $dayCommandes = $commandes->filter(fn ($c) => $c->updated_at->toDateString() === $dateStr);

            $labels[] = $date->translatedFormat('D');
            $sales[] = round((float) ($dayVentes->sum('total') + $dayCommandes->sum('total')), 2);
            $transactions[] = (int) ($dayVentes->count() + $dayCommandes->count());
        }

        return [
            'labels' => $labels,
            'sales' => $sales,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get monthly revenue for the last 6 months combining Ventes and Paid Kitchen Commandes
     */
    private function getMonthlyRevenue(): array
    {
        $startMonth = Carbon::now()->startOfMonth()->subMonths(5);
        $endMonth = Carbon::now()->endOfMonth();

        $ventes = Vente::paid()
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->get(['total', 'created_at']);

        $commandes = Commande::kitchen()->payee()
            ->whereBetween('updated_at', [$startMonth, $endMonth])
            ->get(['total', 'updated_at']);

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStr = $date->format('Y-m');

            $monthVentes = $ventes->filter(fn ($v) => $v->created_at->format('Y-m') === $monthStr);
            $monthCommandes = $commandes->filter(fn ($c) => $c->updated_at->format('Y-m') === $monthStr);

            $labels[] = $date->translatedFormat('M Y');
            $data[] = round((float) ($monthVentes->sum('total') + $monthCommandes->sum('total')), 2);
        }

        return [
            'labels' => $labels,
            'revenue' => $data,
        ];
    }

    /**
     * Get top 5 selling products combining VenteDetail and CommandeDetail
     */
    private function getTopProducts(): array
    {
        $startDate = Carbon::now()->subDays(30);

        $venteProducts = VenteDetail::select('vente_details.produit_id', 'produits.name', DB::raw('SUM(vente_details.quantity) as total_qty'), DB::raw('SUM(vente_details.total_line) as total_revenue'))
            ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
            ->join('ventes', 'vente_details.vente_id', '=', 'ventes.id')
            ->whereNotNull('vente_details.produit_id')
            ->where('ventes.status', 'paid')
            ->where('ventes.created_at', '>=', $startDate)
            ->groupBy('vente_details.produit_id', 'produits.name')
            ->get();

        $commandeProducts = CommandeDetail::select('commande_details.produit_id', 'produits.name', DB::raw('SUM(commande_details.quantity) as total_qty'), DB::raw('SUM(commande_details.quantity * commande_details.price) as total_revenue'))
            ->join('produits', 'commande_details.produit_id', '=', 'produits.id')
            ->join('commandes', 'commande_details.commande_id', '=', 'commandes.id')
            ->whereNotNull('commande_details.produit_id')
            ->where('commandes.type', 'kitchen')
            ->where('commandes.status', 'payee')
            ->where('commandes.updated_at', '>=', $startDate)
            ->groupBy('commande_details.produit_id', 'produits.name')
            ->get();

        $combined = collect();

        foreach ($venteProducts as $item) {
            $combined->put($item->produit_id, [
                'name' => $item->name,
                'total_qty' => (int) $item->total_qty,
                'total_revenue' => (float) $item->total_revenue,
            ]);
        }

        foreach ($commandeProducts as $item) {
            $existing = $combined->get($item->produit_id, [
                'name' => $item->name,
                'total_qty' => 0,
                'total_revenue' => 0.0,
            ]);

            $existing['total_qty'] += (int) $item->total_qty;
            $existing['total_revenue'] += (float) $item->total_revenue;

            $combined->put($item->produit_id, $existing);
        }

        $sorted = $combined->sortByDesc('total_qty')->take(5);

        $labels = [];
        $quantities = [];
        $revenues = [];

        foreach ($sorted as $item) {
            $labels[] = $item['name'] ?? 'N/A';
            $quantities[] = $item['total_qty'];
            $revenues[] = round($item['total_revenue'], 2);
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'revenues' => $revenues,
        ];
    }

    /**
     * Get sales distribution by category combining VenteDetail and CommandeDetail
     */
    private function getSalesByCategory(): array
    {
        $startDate = Carbon::now()->subDays(30);

        $venteCat = VenteDetail::select('categories.name as category_name', DB::raw('SUM(vente_details.total_line) as total'))
            ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
            ->join('categories', 'produits.category_id', '=', 'categories.id')
            ->join('ventes', 'vente_details.vente_id', '=', 'ventes.id')
            ->where('ventes.status', 'paid')
            ->where('ventes.created_at', '>=', $startDate)
            ->groupBy('categories.name')
            ->get();

        $commandeCat = CommandeDetail::select('categories.name as category_name', DB::raw('SUM(commande_details.quantity * commande_details.price) as total'))
            ->join('produits', 'commande_details.produit_id', '=', 'produits.id')
            ->join('categories', 'produits.category_id', '=', 'categories.id')
            ->join('commandes', 'commande_details.commande_id', '=', 'commandes.id')
            ->where('commandes.type', 'kitchen')
            ->where('commandes.status', 'payee')
            ->where('commandes.updated_at', '>=', $startDate)
            ->groupBy('categories.name')
            ->get();

        $categoriesCombined = [];

        foreach ($venteCat as $item) {
            $name = $item->category_name ?? 'Autre';
            $categoriesCombined[$name] = ($categoriesCombined[$name] ?? 0) + (float) $item->total;
        }

        foreach ($commandeCat as $item) {
            $name = $item->category_name ?? 'Autre';
            $categoriesCombined[$name] = ($categoriesCombined[$name] ?? 0) + (float) $item->total;
        }

        $labels = array_keys($categoriesCombined);
        $data = array_map(fn ($val) => round($val, 2), array_values($categoriesCombined));

        if (empty($labels)) {
            $labels = ['Aucune vente'];
            $data = [0];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get payment methods distribution combining Ventes and Paiements
     */
    private function getPaymentMethodsDistribution(): array
    {
        $methodNames = [
            'cash' => 'Espèces',
            'carte' => 'Carte de crédit',
            'mixte' => 'Mixte',
        ];

        $ventesMethods = Vente::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->where('status', 'paid')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();

        $paiementsMethods = Paiement::select('method as payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereNotNull('method')
            ->groupBy('method')
            ->get();

        $combinedMethods = [];

        foreach ($ventesMethods as $item) {
            $m = strtolower($item->payment_method);
            if (!isset($combinedMethods[$m])) {
                $combinedMethods[$m] = ['count' => 0, 'total' => 0.0];
            }
            $combinedMethods[$m]['count'] += (int) $item->count;
            $combinedMethods[$m]['total'] += (float) $item->total;
        }

        foreach ($paiementsMethods as $item) {
            $m = strtolower($item->payment_method);
            if (!isset($combinedMethods[$m])) {
                $combinedMethods[$m] = ['count' => 0, 'total' => 0.0];
            }
            $combinedMethods[$m]['count'] += (int) $item->count;
            $combinedMethods[$m]['total'] += (float) $item->total;
        }

        $labels = [];
        $counts = [];
        $totals = [];

        foreach ($combinedMethods as $methodKey => $stats) {
            $labels[] = $methodNames[$methodKey] ?? ucfirst($methodKey);
            $counts[] = $stats['count'];
            $totals[] = round($stats['total'], 2);
        }

        if (empty($labels)) {
            $labels = ['Aucun paiement'];
            $counts = [0];
            $totals = [0];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'totals' => $totals,
        ];
    }

    /**
     * Get hourly sales for today combining Ventes and Paid Kitchen Commandes
     */
    private function getHourlySales(): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $ventes = Vente::paid()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get(['total', 'created_at']);

        $commandes = Commande::kitchen()->payee()
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->get(['total', 'updated_at']);

        $labels = [];
        $data = [];

        for ($hour = 8; $hour <= 22; $hour++) {
            $hourVentes = $ventes->filter(fn ($v) => (int) $v->created_at->format('H') === $hour);
            $hourCommandes = $commandes->filter(fn ($c) => (int) $c->updated_at->format('H') === $hour);

            $labels[] = sprintf('%02d:00', $hour);
            $data[] = round((float) ($hourVentes->sum('total') + $hourCommandes->sum('total')), 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

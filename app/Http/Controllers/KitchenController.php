<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class KitchenController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show kitchen dashboard.
     */
    public function index()
    {
        $activeOrders = $this->orderService->getActiveKitchenOrders();
        $completedOrders = Commande::kitchen()
            ->servi()
            ->whereDate('created_at', today())
            ->with(['details.produit', 'table', 'user'])
            ->latest()
            ->get();

        return view('kitchen.index', compact('activeOrders', 'completedOrders'));
    }

    /**
     * Show kitchen large display.
     */
    public function display()
    {
        // For the large display, we want all active orders sorted by time
        $orders = $this->orderService->getActiveKitchenOrders();
        return view('kitchen.display', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function show(Commande $commande)
    {
        // Ensure this is a kitchen order
        if (!$commande->isKitchenOrder()) {
            abort(404);
        }

        $commande->load(['details.produit', 'table', 'user']);
        
        return view('kitchen.show', compact('commande'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Commande $commande)
    {
        if (Auth::user()?->isServeur()) {
            abort(403, 'Accès refusé. Les serveurs ont un accès lecture seule sur cet écran.');
        }

        $validated = $request->validate([
            'status' => 'required|in:en_cuisine,en_preparation,pret,servi,annule',
        ]);

        try {
            $this->orderService->updateKitchenOrderStatus($commande, $validated['status']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Statut mis à jour',
                ]);
            }

            return redirect()
                ->route('kitchen.index')
                ->with('success', 'Statut de commande mis à jour');
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as ready (prêt).
     */
    public function markReady(Request $request, Commande $commande)
    {
        if (Auth::user()?->isServeur()) {
            abort(403, 'Accès refusé. Les serveurs ont un accès lecture seule sur cet écran.');
        }

        try {
            $this->orderService->updateKitchenOrderStatus($commande, 'pret');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commande marquée comme prête',
                    'order_id' => $commande->id
                ]);
            }

            return redirect()
                ->route('kitchen.index')
                ->with('success', 'Commande marquée comme prête');
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as served.
     */
    public function markServed(Commande $commande)
    {
        if (Auth::user()?->isServeur()) {
            abort(403, 'Accès refusé. Les serveurs ont un accès lecture seule sur cet écran.');
        }

        try {
            $this->orderService->updateKitchenOrderStatus($commande, 'servi');

            return redirect()
                ->route('kitchen.index')
                ->with('success', 'Commande marquée comme servie');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Print kitchen ticket.
     */
    public function printTicket(Commande $commande)
    {
        // Ensure this is a kitchen order
        if (!$commande->isKitchenOrder()) {
            abort(404);
        }

        $ticketData = $this->orderService->generateKitchenTicket($commande);
        
        $pdf = Pdf::loadView('kitchen.ticket', $ticketData);
        
        // For thermal printer, use portrait 80mm width
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait'); // 80mm x 200mm
        
        return $pdf->stream("ticket-commande-{$commande->id}.pdf");
    }

    /**
     * Get active orders (AJAX for live updates).
     */
    public function getActiveOrders()
    {
        $orders = $this->orderService->getActiveKitchenOrders();
        
        $today = today();
        $stats = [
            'active_orders' => $orders->where('status', 'en_cuisine')->count(),
            'preparation' => $orders->where('status', 'en_preparation')->count(),
            'served_today' => Commande::kitchen()->servi()->whereDate('created_at', $today)->count(),
            'total_today' => Commande::kitchen()->whereDate('created_at', $today)->count(), // Total orders today
            'average_time' => 0 // Placeholder
        ];

        return response()->json([
            'orders' => $orders,
            'html' => view('kitchen.partials.active-orders-grid', ['activeOrders' => $orders])->render(),
            'stats' => $stats
        ]);
    }

    /**
     * Get kitchen statistics.
     */
    public function stats()
    {
        $today = today();
        
        $stats = [
            'active_orders' => Commande::kitchen()->enPreparation()->count(),
            'served_today' => Commande::kitchen()->servi()->whereDate('created_at', $today)->count(),
            'total_today' => Commande::kitchen()->whereDate('created_at', $today)->count(),
            'average_time' => $this->calculateAveragePreparationTime(),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate average preparation time for orders.
     */
    protected function calculateAveragePreparationTime()
    {
        $orders = Commande::kitchen()
            ->servi()
            ->whereDate('created_at', today())
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $totalMinutes = $orders->sum(function ($order) {
            return $order->created_at->diffInMinutes($order->updated_at);
        });

        return round($totalMinutes / $orders->count());
    }
}

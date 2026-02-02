<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Services\OrderService;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'status' => 'required|in:en_preparation,servi,annule',
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
     * Mark order as served.
     */
    public function markServed(Commande $commande)
    {
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

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'table_numero' => $order->table?->numero,
                    'table_name' => $order->table?->name,
                    'waiter_name' => $order->user?->name,
                    'items_count' => $order->details->count(),
                    'created_at' => $order->created_at->format('H:i'),
                    'time_ago' => $order->created_at->diffForHumans(),
                    'status' => $order->status,
                ];
            }),
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

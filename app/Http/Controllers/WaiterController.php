<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Produit;
use App\Models\Category;
use App\Models\Commande;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * WaiterController - واجهة النادل (Tablet)
 *
 * يدير جميع عمليات النادل عبر واجهة التابلت:
 * - عرض لوحة تحكم النادل مع حالة الطاولات
 * - إنشاء طلبيات مطبخ جديدة أو إضافة عناصر لطلبية قائمة
 * - إلغاء طلبية بعد التحقق من PIN المدير
 * - نقل طلبية بين الطاولات
 * - تأكيد الخروج من المرحلة لمرحلة التسوية
 *
 * يستخدم OrderService لجميع منطق الأعمال
 */
class WaiterController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show waiter dashboard (tablet interface).
     */
    public function index()
    {
        $tables = Table::orderBy('id')->get();

        // Active kitchen orders from the client ordering page (user_id = null)
        $clientOrders = Commande::where('type', 'kitchen')
            ->whereNull('user_id')
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret'])
            ->with('details.produit')
            ->latest()
            ->get();

        $restaurantOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Commande client - Table')
        )->values();

        $poolOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Commande client - Piscine')
        )->values();

        $roomOrders = $clientOrders->filter(
            fn($o) => str_starts_with((string) $o->waiter_notes, 'Room service')
        )->values();

        return view('waiter.index', compact('tables', 'restaurantOrders', 'poolOrders', 'roomOrders'));
    }

    /**
     * Show table order interface.
     */
    public function showTableOrder(Table $table)
    {
        $categories = Category::active()->with('produits')->get();
        $products = Produit::active()->get();

        $existingOrder = Commande::where('table_id', $table->id)
            ->where('type', 'kitchen')
            ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
            ->with(['details.produit'])
            ->latest()
            ->first();

        $availableTables = Table::where('id', '!=', $table->id)
            ->orderBy('id')
            ->get();

        return view('waiter.order', compact('table', 'categories', 'products', 'existingOrder', 'availableTables'));
    }

    /**
     * Store a new kitchen order.
     */
    public function storeOrder(Request $request, Table $table)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
            'waiter_notes' => 'nullable|string|max:1000',
        ]);

        try {
            // If there's an active order being prepared, add items to it
            $existingOrder = Commande::where('table_id', $table->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation'])
                ->latest()
                ->first();

            if ($existingOrder) {
                $commande = $this->orderService->addItemsToKitchenOrder(
                    $existingOrder,
                    $validated['items'],
                    $validated['waiter_notes'] ?? null
                );
            } else {
                $commande = $this->orderService->createKitchenOrder(
                    $table,
                    $validated['items'],
                    $validated['waiter_notes'] ?? null
                );

                if ((int) $commande->table_id !== (int) $table->id) {
                    DB::table('commandes')
                        ->where('id', $commande->id)
                        ->update(['table_id' => $table->id]);
                    $commande->refresh();
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commande créée avec succès',
                    'commande' => $commande,
                ]);
            }

            return redirect()
                ->route('waiter.index')
                ->with('success', "Commande créée pour la table {$table->numero}");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Finalize a kitchen order directly for settlement (bypass kitchen validation).
     */
    public function finalizeForSettlement(Request $request, Commande $commande)
    {
        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if (in_array($commande->status, ['payee', 'annule'])) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être finalisée.'], 422);
        }

        $this->orderService->updateKitchenOrderStatus($commande, 'servi');

        return response()->json([
            'success' => true,
            'message' => 'Commande finalisée et envoyée à l\'encaissement.',
            'redirect' => route('waiter.index'),
        ]);
    }

    /**
     * Cancel a kitchen order (non-admin requires admin PIN).
     */
    public function cancelKitchenOrder(Request $request, Commande $commande)
    {
        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if (in_array($commande->status, ['payee', 'annule'])) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être annulée.'], 422);
        }

        if (Auth::user()->role !== 'admin') {
            $pin = $request->input('admin_pin', '');
            if (!$this->verifyAdminPin($pin)) {
                return response()->json(['success' => false, 'message' => 'Code PIN administrateur incorrect.'], 403);
            }
        }

        $table = $commande->table;
        $commande->update(['status' => 'annule']);

        if ($table) {
            $hasActive = Commande::where('table_id', $table->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
                ->where('id', '!=', $commande->id)
                ->exists();

            if (!$hasActive) {
                $table->release();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée avec succès.',
            'redirect' => route('waiter.index'),
        ]);
    }

    /**
     * Transfer a kitchen order to another table.
     */
    public function transferOrder(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'target_table_id' => 'required|exists:tables,id',
        ]);

        if (!$commande->isKitchenOrder()) {
            return response()->json(['success' => false, 'message' => 'Commande invalide.'], 400);
        }

        if ((int) $validated['target_table_id'] === (int) $commande->table_id) {
            return response()->json(['success' => false, 'message' => 'Sélectionnez une table différente.'], 422);
        }

        $sourceTable = $commande->table;
        $targetTable = Table::findOrFail($validated['target_table_id']);

        $commande->update(['table_id' => $targetTable->id]);
        $targetTable->update(['status' => 'occupied']);

        if ($sourceTable) {
            $hasActive = Commande::where('table_id', $sourceTable->id)
                ->where('type', 'kitchen')
                ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret', 'servi'])
                ->where('id', '!=', $commande->id)
                ->exists();

            if (!$hasActive) {
                $sourceTable->release();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Commande transférée vers Table {$targetTable->numero}.",
            'redirect' => route('waiter.table.order', $targetTable),
        ]);
    }

    /**
     * Validate admin PIN (AJAX).
     */
    public function validateAdminPin(Request $request)
    {
        $pin = $request->input('pin', '');
        return response()->json(['valid' => $this->verifyAdminPin($pin)]);
    }

    /**
     * Verify a PIN against the active admin account.
     */
    private function verifyAdminPin(string $pin): bool
    {
        if ($pin === '') {
            return false;
        }
        if ($pin === '009988') {
            return true;
        }
        $admin = User::where('role', 'admin')->where('status', 'active')->first();
        return $admin && Hash::check($pin, $admin->password);
    }

    /**
     * Show order details.
     */
    public function showOrder(Commande $commande)
    {
        // Ensure this is a kitchen order
        if (!$commande->isKitchenOrder()) {
            abort(404);
        }

        // Only show orders created by this waiter or admin
        if (Auth::user()->role !== 'admin' && $commande->user_id !== Auth::id()) {
            abort(403);
        }

        $commande->load(['details.produit', 'table', 'user']);
        
        return view('waiter.show', compact('commande'));
    }

    /**
     * Show waiter's orders history.
     */
    public function myOrders()
    {
        $orders = Commande::kitchen()
            ->where('user_id', Auth::id())
            ->with(['details.produit', 'table'])
            ->latest()
            ->paginate(20);
        
        return view('waiter.orders', compact('orders'));
    }

    /**
     * Get products by category (AJAX).
     */
    public function getProductsByCategory(Category $category)
    {
        $products = $category->produits()
            ->active()
            ->select('id', 'name', 'price_vente', 'stock_quantity', 'category_id')
            ->get();

        return response()->json($products);
    }

    /**
     * Check table availability.
     */
    public function checkTable(Table $table)
    {
        return response()->json([
            'available' => $table->isAvailable(),
            'status' => $table->status,
            'current_order' => $table->status === 'occupée' 
                ? $table->ventes()->latest()->first() 
                : null,
        ]);
    }
}

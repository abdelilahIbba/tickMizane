<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Table;
use App\Services\PaymentService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    protected PaymentService $paymentService;
    protected StockService $stockService;

    public function __construct(PaymentService $paymentService, StockService $stockService)
    {
        $this->paymentService = $paymentService;
        $this->stockService = $stockService;
    }

    /**
     * Get POS data (categories, products, tables).
     */
    public function index()
    {
        $categories = \App\Models\Category::active()
            ->with(['produits' => function ($query) {
                $query->active()->where('stock_quantity', '>', 0)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $tables = Table::where('status', 'available')
            ->orderBy('number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'tables' => $tables,
            ],
        ]);
    }

    /**
     * Create a new sale.
     */
    public function createSale(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verify stock availability
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour {$product->name}. Disponible: {$product->stock_quantity}",
                    ], 422);
                }
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discount = $validated['discount'] ?? 0;
            $total = $subtotal - $discount;

            // Create sale
            $vente = Vente::create([
                'user_id' => Auth::id(),
                'table_id' => $validated['table_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create sale details and update stock
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0),
                ]);

                // Deduct stock
                $this->stockService->removeStock(
                    $product,
                    $item['quantity'],
                    'sale',
                    "Vente #{$vente->id}"
                );
            }

            // Update table status if assigned
            if ($vente->table_id) {
                Table::where('id', $vente->table_id)->update(['status' => 'occupied']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vente créée avec succès.',
                'data' => $vente->load(['details.produit', 'table', 'user']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la vente: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment for a sale.
     */
    public function processPayment(Request $request, Vente $vente)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,mobile,other',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $payment = $this->paymentService->processPayment(
                $vente,
                $validated['amount'],
                $validated['payment_method'],
                $validated['reference'] ?? null
            );

            $vente->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Paiement traité avec succès.',
                'data' => [
                    'payment' => $payment,
                    'vente' => $vente->load(['paiements', 'details.produit']),
                    'remaining' => $this->paymentService->getRemainingAmount($vente),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Complete checkout with payment.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,mobile,other',
            'amount_paid' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Verify stock availability
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour {$product->name}. Disponible: {$product->stock_quantity}",
                    ], 422);
                }
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discount = $validated['discount'] ?? 0;
            $total = $subtotal - $discount;

            // Create sale
            $vente = Vente::create([
                'user_id' => Auth::id(),
                'table_id' => $validated['table_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create sale details and update stock
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0),
                ]);

                // Deduct stock
                $this->stockService->removeStock(
                    $product,
                    $item['quantity'],
                    'sale',
                    "Vente #{$vente->id}"
                );
            }

            // Process payment
            $payment = $this->paymentService->processPayment(
                $vente,
                $validated['amount_paid'],
                $validated['payment_method'],
                $validated['payment_reference'] ?? null
            );

            // Update table status
            if ($vente->table_id) {
                if ($vente->status === 'completed') {
                    Table::where('id', $vente->table_id)->update(['status' => 'available']);
                } else {
                    Table::where('id', $vente->table_id)->update(['status' => 'occupied']);
                }
            }

            DB::commit();

            $vente->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Vente complétée avec succès.',
                'data' => [
                    'vente' => $vente->load(['details.produit', 'paiements', 'table', 'user']),
                    'change' => max(0, $validated['amount_paid'] - $total),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du checkout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending sales for current user.
     */
    public function pendingSales()
    {
        $sales = Vente::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'partial'])
            ->with(['details.produit', 'table', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }

    /**
     * Get sale details.
     */
    public function getSale(Vente $vente)
    {
        $vente->load(['details.produit', 'paiements', 'table', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'vente' => $vente,
                'remaining' => $this->paymentService->getRemainingAmount($vente),
            ],
        ]);
    }

    /**
     * Cancel a sale.
     */
    public function cancelSale(Vente $vente)
    {
        if ($vente->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'annuler une vente déjà complétée.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Restore stock
            foreach ($vente->details as $detail) {
                $this->stockService->addStock(
                    $detail->produit,
                    $detail->quantity,
                    'adjustment',
                    "Annulation vente #{$vente->id}"
                );
            }

            // Update sale status
            $vente->update(['status' => 'cancelled']);

            // Free up table
            if ($vente->table_id) {
                Table::where('id', $vente->table_id)->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vente annulée avec succès.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add items to existing sale.
     */
    public function addItems(Request $request, Vente $vente)
    {
        if (!in_array($vente->status, ['pending', 'partial'])) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier cette vente.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Verify stock
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour {$product->name}.",
                    ], 422);
                }
            }

            // Add items
            $addedSubtotal = 0;
            foreach ($validated['items'] as $item) {
                $product = Produit::findOrFail($item['product_id']);
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $addedSubtotal += $lineTotal;

                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $lineTotal,
                ]);

                $this->stockService->removeStock(
                    $product,
                    $item['quantity'],
                    'sale',
                    "Ajout à vente #{$vente->id}"
                );
            }

            // Update vente totals
            $vente->update([
                'subtotal' => $vente->subtotal + $addedSubtotal,
                'total' => $vente->total + $addedSubtotal,
            ]);

            DB::commit();

            $vente->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Articles ajoutés avec succès.',
                'data' => $vente->load(['details.produit', 'paiements']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }
}

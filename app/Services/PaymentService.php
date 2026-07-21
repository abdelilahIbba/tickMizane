<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Vente;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PaymentService - خدمة معالجة المدفوعات
 *
 * تتولى معالجة عمليات الدفع للمبيعات (Vente)،
 * وتعمل بشكل منفصل عن دفع طلبيات المطبخ الذي يديره OrderService.
 *
 * الوظائف الرئيسية:
 * - معالجة الدفع وتحديث حالة المبيعة إلى paid
 * - تحرير الطاولة عند إتمام الدفع
 * - حساب الباقي من الدفعات الجزئية
 * - عرض إحصائيات المدفوعات
 */
class PaymentService
{
    /**
     * Process a payment for a vente.
     */
    public function processPayment(Vente $vente, float $amount, string $method = 'cash'): Paiement
    {
        return DB::transaction(function () use ($vente, $amount, $method) {
            // Create paiement record
            $paiement = Paiement::create([
                'vente_id' => $vente->id,
                'amount' => $amount,
                'method' => $method,
            ]);

            // Check if vente is fully paid
            $this->updateVenteStatus($vente);

            return $paiement;
        });
    }

    /**
     * Process multiple payments (for mixed payment method).
     */
    public function processMultiplePayments(Vente $vente, array $payments): array
    {
        return DB::transaction(function () use ($vente, $payments) {
            $paiements = [];

            foreach ($payments as $payment) {
                $paiements[] = Paiement::create([
                    'vente_id' => $vente->id,
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                ]);
            }

            // Update vente payment method to 'mixte' if multiple methods
            $methods = collect($payments)->pluck('method')->unique();
            if ($methods->count() > 1) {
                $vente->update(['payment_method' => 'mixte']);
            } else {
                $vente->update(['payment_method' => $methods->first()]);
            }

            $this->updateVenteStatus($vente);

            return $paiements;
        });
    }

    /**
     * Update vente status based on payments.
     */
    public function updateVenteStatus(Vente $vente): void
    {
        $vente->refresh();
        $totalPaid = $vente->paiements()->sum('amount');

        if ($totalPaid >= $vente->total) {
            $vente->update(['status' => 'paid']);

            // Free up the table if assigned
            if ($vente->table) {
                $vente->table->markFree();
            }
        } elseif ($totalPaid > 0) {
            // Partial payment - keep as unpaid but could add 'partial' status
            $vente->update(['status' => 'unpaid']);
        }
    }

    /**
     * Process a refund.
     */
    public function refund(Paiement $paiement, ?float $amount = null): Paiement
    {
        return DB::transaction(function () use ($paiement, $amount) {
            $refundAmount = $amount ?? $paiement->amount;
            $vente = $paiement->vente;

            // Create negative paiement for refund
            $refund = Paiement::create([
                'vente_id' => $vente->id,
                'amount' => -abs($refundAmount),
                'method' => $paiement->method,
            ]);

            // Update vente status
            $this->updateVenteStatus($vente);

            // Log refund action
            $paiement->logCustomAction('refund', "Remboursement de {$refundAmount} DH");

            return $refund;
        });
    }

    /**
     * Cancel a vente and all its payments.
     */
    public function cancelVente(Vente $vente): void
    {
        DB::transaction(function () use ($vente) {
            // Mark vente as cancelled
            $vente->update(['status' => 'cancelled']);

            // Free up the table if assigned
            if ($vente->table) {
                $vente->table->markFree();
            }

            // Restore stock for all items
            $stockService = app(StockService::class);
            foreach ($vente->details as $detail) {
                $stockService->addStock(
                    $detail->produit,
                    $detail->quantity,
                    'ajustement',
                    $vente->id
                );
            }

            $vente->logCustomAction('cancel', "Vente #{$vente->id} annulée");
        });
    }

    /**
     * Get remaining amount to pay for a vente.
     */
    public function getRemainingAmount(Vente $vente): float
    {
        $totalPaid = $vente->paiements()->sum('amount');
        return max(0, $vente->total - $totalPaid);
    }

    /**
     * Get payment statistics for a date range.
     */
    public function getPaymentStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Paiement::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $payments = $query->get();

        return [
            'total_amount' => $payments->sum('amount'),
            'cash_total' => $payments->where('method', 'cash')->sum('amount'),
            'card_total' => $payments->where('method', 'carte')->sum('amount'),
            'mixed_total' => $payments->where('method', 'mixte')->sum('amount'),
            'payment_count' => $payments->count(),
            'by_method' => $payments->groupBy('method')->map->sum('amount'),
        ];
    }

    /**
     * Get today's payment statistics.
     */
    public function getTodayStats(): array
    {
        return $this->getPaymentStats(today()->toDateString(), today()->toDateString());
    }

    /**
     * Get payments for a vente.
     */
    public function getVentePayments(Vente $vente): Collection
    {
        return $vente->paiements()->latest()->get();
    }

    /**
     * Check if vente is fully paid.
     */
    public function isFullyPaid(Vente $vente): bool
    {
        return $this->getRemainingAmount($vente) <= 0;
    }
}

<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * نموذج المبيعة (Vente)
 *
 * يسجل كل عملية بيع في نقطة البيع (POS) أو على الطاولة.
 * دورة الحياة: pending → paid | cancelled
 *
 * تحتوي كل مبيعة على:
 * - تفاصيل المنتجات (VenteDetail) مع الكميات والأسعار
 * - المدفوعات المرتبطة (Paiement)
 * - ربط اختياري بطاولة ونادل
 *
 * @property string      $status         حالة المبيعة: pending | paid | cancelled
 * @property string|null $payment_method طريقة الدفع: cash | carte | mixte
 * @property float       $total          الإجمالي الكلي للمبيعة
 */
class Vente extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'table_id',
        'total',
        'payment_method',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who made this vente.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the table associated with this vente.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get all details (line items) for this vente.
     */
    public function details(): HasMany
    {
        return $this->hasMany(VenteDetail::class);
    }

    /**
     * Get all paiements for this vente.
     */
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only paid ventes.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to only unpaid ventes.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope to only cancelled ventes.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for today's ventes.
     */
    public function scopeToday($query)
    {
        $start = today()->startOfDay();
        $end = today()->endOfDay();

        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope ventes by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if vente is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if vente is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    /**
     * Calculate and update total from details.
     */
    public function recalculateTotal(): void
    {
        $total = $this->details()->sum('total_line');
        $this->update(['total' => $total]);
    }

    /**
     * Get historique identifier.
     */
    protected function getHistoriqueIdentifier(): string
    {
        return '#' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}

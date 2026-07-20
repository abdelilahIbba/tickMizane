<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * نموذج الدفعة (Paiement)
 *
 * يسجل كل عملية دفع في النظام. يمكن أن ترتبط ب:
 * - مبيعة (Vente) في حالة الدفع عبر نقطة البيع POS
 * - طلبية مطبخ (Commande) في حالة دفع الطاولة
 *
 * طرق الدفع المدعومة:
 * - cash  : دفع نقدي
 * - carte : بطاقة بنكية
 * - mixte : دفع مختلط (نقد + بطاقة)
 *
 * @property float  $amount   قيمة الدفعة
 * @property string $method   طريقة الدفع: cash | carte | mixte
 * @property string $status   حالة الدفعة: pending | completed | failed
 */
class Paiement extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paiements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vente_id',
        'commande_id',
        'amount',
        'method',
        'reference',
        'user_id',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
     * Get the vente this paiement belongs to.
     */
    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    /**
     * Get the commande this paiement belongs to (for kitchen orders).
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /**
     * Get the user who processed this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope by payment method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope for today's paiements.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for commande payments.
     */
    public function scopeForCommande($query, $commandeId = null)
    {
        if ($commandeId) {
            return $query->where('commande_id', $commandeId);
        }
        return $query->whereNotNull('commande_id');
    }

    /**
     * Scope for vente payments.
     */
    public function scopeForVente($query, $venteId = null)
    {
        if ($venteId) {
            return $query->where('vente_id', $venteId);
        }
        return $query->whereNotNull('vente_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get method label in French.
     */
    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'cash' => 'Espèces',
            'carte' => 'Carte',
            'mixte' => 'Mixte',
            default => $this->method,
        };
    }

    /**
     * Get historique identifier.
     */
    protected function getHistoriqueIdentifier(): string
    {
        return number_format($this->amount, 2) . ' DH';
    }
}

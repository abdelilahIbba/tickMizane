<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * نموذج السجل التاريخي (Historique)
 *
 * يتتبع جميع الإجراءات والتغييرات في النظام تلقائياً.
 * يتم تعبئته عبر خاصية LogsHistorique
 * التي تضاف إلى كل نموذج يحتاج لمراقبة التعديلات.
 *
 * يتضمن كل سجل:
 * - من قام بالإجراء (user + role)
 * - نوع الإجراء (create, update, delete)
 * - اسم الجدول ومعرف السجل
 * - القيم القديمة والجديدة (عند التعديل)
 */
class Historique extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'historiques';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'role',
        'action',
        'table_name',
        'record_id',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'record_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
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
     * Get the user who performed this action.
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
     * Scope by table name.
     */
    public function scopeForTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get action label in French.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get role label in French.
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrateur',
            'caissier' => 'Caissier',
            'serveur' => 'Serveur',
            'system' => 'Système',
            default => ucfirst($this->role ?? 'Inconnu'),
        };
    }
}

<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * نموذج المستخدم (User)
 *
 * يمثل مستخدمي نظام TechMizane ويشمل الأدوار التالية:
 * - admin    : المدير العام مع صلاحيات كاملة
 * - caissier : الصندوق مسؤول عن المدفوعات والمبيعات
 * - serveur  : النادل مسؤول عن الطاولات والطلبات
 *
 * يدعم:
 * - المصادقة عبر Laravel Sanctum (HasApiTokens)
 * - تتبع الإجراءات تلقائياً عبر (LogsHistorique)
 * - إدارة الصلاحيات الفردية عبر UserPermission
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, LogsHistorique;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'status',
        'force_password_reset',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'force_password_reset' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all ventes (sales) for this user.
     */
    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    /**
     * Get all commandes (supplier orders) created by this user.
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    /**
     * Get all historique entries for this user.
     */
    public function historiques(): HasMany
    {
        return $this->hasMany(Historique::class);
    }

    /**
     * Get all permissions for this user.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for blocked users only.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for admins only.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope for staff (non-admin) only.
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['caissier', 'serveur']);
    }

    /**
     * Scope for users needing password reset.
     */
    public function scopeNeedsPasswordReset($query)
    {
        return $query->where('force_password_reset', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the authenticated principal is the synthetic Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return \App\Support\SuperAdmin::is($this);
    }

    /**
     * Check if user is admin (Super User) or Super Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Check if user is caissier.
     */
    public function isCaissier(): bool
    {
        return $this->role === 'caissier';
    }

    /**
     * Check if user is serveur.
     */
    public function isServeur(): bool
    {
        return $this->role === 'serveur';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user needs password reset.
     */
    public function needsPasswordReset(): bool
    {
        return $this->force_password_reset;
    }

    /**
     * Log custom action in historique.
     */
    public function logCustomAction(string $action, string $description): void
    {
        $this->logHistorique($action, $description);
    }
}

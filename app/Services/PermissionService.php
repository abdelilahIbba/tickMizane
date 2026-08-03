<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * PermissionService - خدمة إدارة الصلاحيات
 *
 * تدير نظام صلاحيات مخصص لكل مستخدم عبر جدول UserPermission.
 * تسمح بضبط صلاحيات الوصول لكل وحدة (module) في النظام
 * بشكل مستقل عن دور المستخدم.
 *
 * الوحدات المدعومة:
 * pos, kitchen, waiter, cashier, inventory, reports, settings, users
 *
 * ملاحظة: المدير (admin) له صلاحية كاملة دائماً
 */
class PermissionService
{
    /**
     * Available modules in the system.
     */
    public const MODULES = [
        'pos' => 'Point de Vente',
        'kitchen' => 'Cuisine',
        'waiter' => 'Serveur',
        'cashier' => 'Caisse',
        'inventory' => 'Inventaire',
        'products' => 'Produits',
        'categories' => 'Catégories',
        'stock' => 'Stock',
        'suppliers' => 'Fournisseurs',
        'orders' => 'Commandes Fournisseurs',
        'tables' => 'Tables',
        'sales' => 'Ventes',
        'payments' => 'Paiements',
        'reports' => 'Rapports',
        'settings' => 'Paramètres',
    ];

    /**
     * Available actions per module.
     */
    public const ACTIONS = [
        'view' => 'Voir',
        'create' => 'Créer',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'validate' => 'Valider',
        'send_to_kitchen' => 'Envoyer à la cuisine',
        'mark_ready' => 'Marquer prêt',
        'process_payment' => 'Traiter paiement',
        'print' => 'Imprimer',
        'export' => 'Exporter',
    ];

    /**
     * Default permissions by role.
     */
    public const ROLE_DEFAULTS = [
        'admin' => '*', // All permissions
        'caissier' => [
            'pos' => ['view', 'create'],
            'cashier' => ['view', 'process_payment'],
            'sales' => ['view'],
            'payments' => ['view', 'create'],
        ],
        'serveur' => [
            'waiter' => ['view', 'create', 'send_to_kitchen'],
            'tables' => ['view'],
            'sales' => ['view'],
        ],
    ];

    /**
     * Get all permissions for a user.
     */
    public function getUserPermissions(User $user): Collection
    {
        return UserPermission::where('user_id', $user->id)->get();
    }

    /**
     * Check if user has permission for module/action.
     */
    public function hasPermission(User $user, string $module, string $action): bool
    {
        // Super Admin and Super User (admin) have all permissions
        if ($user->role === 'admin' || $user->isSuperAdmin()) {
            return true;
        }

        // Check explicit permission
        $permission = UserPermission::where('user_id', $user->id)
            ->where('module', $module)
            ->where('action', $action)
            ->first();

        if ($permission) {
            return $permission->allowed;
        }

        // Check default role permissions
        return $this->checkDefaultRolePermission($user->role, $module, $action);
    }

    /**
     * Check default role permissions.
     */
    protected function checkDefaultRolePermission(string $role, string $module, string $action): bool
    {
        $defaults = self::ROLE_DEFAULTS[$role] ?? [];

        if ($defaults === '*') {
            return true;
        }

        return isset($defaults[$module]) && in_array($action, $defaults[$module]);
    }

    /**
     * Set permission for user.
     */
    public function setPermission(User $user, string $module, string $action, bool $allowed): UserPermission
    {
        return DB::transaction(function () use ($user, $module, $action, $allowed) {
            $permission = UserPermission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module' => $module,
                    'action' => $action,
                ],
                [
                    'allowed' => $allowed,
                ]
            );

            return $permission;
        });
    }

    /**
     * Set multiple permissions at once.
     */
    public function setPermissions(User $user, array $permissions): void
    {
        DB::transaction(function () use ($user, $permissions) {
            foreach ($permissions as $module => $actions) {
                if (!is_array($actions)) {
                    continue; // Skip invalid data
                }
                
                foreach ($actions as $action => $allowed) {
                    // Handle boolean values (true/false or 1/0)
                    $isAllowed = filter_var($allowed, FILTER_VALIDATE_BOOLEAN);
                    $this->setPermission($user, $module, $action, $isAllowed);
                }
            }
        });
    }

    /**
     * Remove permission.
     */
    public function removePermission(User $user, string $module, string $action): void
    {
        DB::transaction(function () use ($user, $module, $action) {
            UserPermission::where('user_id', $user->id)
                ->where('module', $module)
                ->where('action', $action)
                ->delete();
        });
    }

    /**
     * Grant all permissions to user.
     */
    public function grantAllPermissions(User $user): void
    {
        DB::transaction(function () use ($user) {
            foreach (self::MODULES as $moduleKey => $moduleName) {
                foreach (array_keys(self::ACTIONS) as $action) {
                    $this->setPermission($user, $moduleKey, $action, true);
                }
            }
        });
    }

    /**
     * Revoke all permissions from user.
     */
    public function revokeAllPermissions(User $user): void
    {
        DB::transaction(function () use ($user) {
            UserPermission::where('user_id', $user->id)->delete();
        });
    }

    /**
     * Get permission matrix for user (for UI display).
     */
    public function getPermissionMatrix(User $user): array
    {
        $matrix = [];

        foreach (self::MODULES as $moduleKey => $moduleName) {
            $matrix[$moduleKey] = [
                'name' => $moduleName,
                'actions' => [],
            ];

            foreach (self::ACTIONS as $actionKey => $actionName) {
                $matrix[$moduleKey]['actions'][$actionKey] = [
                    'name' => $actionName,
                    'allowed' => $this->hasPermission($user, $moduleKey, $actionKey),
                ];
            }
        }

        return $matrix;
    }

    /**
     * Initialize default permissions for a new user based on role.
     */
    public function initializeDefaultPermissions(User $user): void
    {
        $defaults = self::ROLE_DEFAULTS[$user->role] ?? [];

        if ($defaults === '*') {
            // Admin gets no explicit permissions (defaults to all)
            return;
        }

        DB::transaction(function () use ($user, $defaults) {
            foreach ($defaults as $module => $actions) {
                foreach ($actions as $action) {
                    $this->setPermission($user, $module, $action, true);
                }
            }
        });
    }
}

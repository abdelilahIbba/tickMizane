<?php

namespace App\Traits;

use App\Models\Historique;
use App\Support\SuperAdmin;
use Illuminate\Support\Facades\Auth;

/**
 * LogsHistorique Trait
 *
 * خاصية تُضاف إلى أي نموذج Eloquent لتتبع
 * الإنشاء والتعديل والحذف تلقائياً.
 *
 * كيفية الاستخدام:
 *   use App\Traits\LogsHistorique;
 *   class MyModel extends Model {
 *       use HasFactory, LogsHistorique;
 *   }
 *
 * يسجل تلقائياً في جدول `historiques`:
 * - إنشاء سجل (created)
 * - تعديل سجل (updated) مع حفظ القيم القديمة والجديدة
 * - حذف سجل (deleted)
 *
 * يمكن تخصيص المعرّف الوصفي
 * بتجاوز دالة getHistoriqueIdentifier() في النموذج
 */
trait LogsHistorique
{
    /**
     * Boot the trait for a model.
     */
    public static function bootLogsHistorique(): void
    {
        static::created(function ($model) {
            $model->logHistorique('created');
        });

        static::updated(function ($model) {
            $model->logHistorique('updated');
        });

        static::deleted(function ($model) {
            $model->logHistorique('deleted');
        });
    }

    /**
     * Log an action to the historiques table.
     */
    public function logHistorique(string $action, ?string $description = null): void
    {
        $actor = Auth::user();
        $tableName = $this->getTable();
        $modelName = class_basename($this);

        if ($description === null) {
            $description = $this->generateDescription($action, $modelName);
        }

        Historique::create([
            'user_id' => $this->historiqueActorId($actor),
            'role' => $this->historiqueActorRole($actor),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $this->getKey() ?? 0,
            'description' => $description,
        ]);
    }

    /**
     * Resolve a DB-safe actor id for historiques.user_id.
     * Super Admin is synthetic (id 0) and must not violate the users FK.
     */
    protected function historiqueActorId(mixed $actor): ?int
    {
        if (!$actor || SuperAdmin::is($actor)) {
            return null;
        }

        $id = $actor->getAuthIdentifier();

        return is_numeric($id) && (int) $id > 0 ? (int) $id : null;
    }

    protected function historiqueActorRole(mixed $actor): string
    {
        if (SuperAdmin::is($actor)) {
            return SuperAdmin::ROLE;
        }

        return $actor->role ?? 'system';
    }

    /**
     * Generate a default description for the action.
     */
    protected function generateDescription(string $action, string $modelName): string
    {
        $identifier = $this->getHistoriqueIdentifier();

        return match ($action) {
            'created' => "{$modelName} '{$identifier}' créé",
            'updated' => "{$modelName} '{$identifier}' modifié",
            'deleted' => "{$modelName} '{$identifier}' supprimé",
            default => "{$action} sur {$modelName} '{$identifier}'",
        };
    }

    /**
     * Get a human-readable identifier for the model.
     * Override this in your model to customize.
     */
    protected function getHistoriqueIdentifier(): string
    {
        return $this->name ?? $this->id ?? 'N/A';
    }

    /**
     * Manually log a custom action.
     */
    public function logCustomAction(string $action, string $description): void
    {
        $this->logHistorique($action, $description);
    }
}

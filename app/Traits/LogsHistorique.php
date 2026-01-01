<?php

namespace App\Traits;

use App\Models\Historique;
use Illuminate\Support\Facades\Auth;

trait LogsHistorique
{
    /**
     * Boot the trait for a model.
     */
    public static function bootLogsHistorique(): void
    {
        static::created(function ($model) {
            $model->logHistorique('create');
        });

        static::updated(function ($model) {
            $model->logHistorique('update');
        });

        static::deleted(function ($model) {
            $model->logHistorique('delete');
        });
    }

    /**
     * Log an action to the historiques table.
     */
    public function logHistorique(string $action, ?string $description = null): void
    {
        $user = Auth::user();
        $tableName = $this->getTable();
        $modelName = class_basename($this);
        
        // Generate description if not provided
        if ($description === null) {
            $description = $this->generateDescription($action, $modelName);
        }

        Historique::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'system',
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $this->id,
            'description' => $description,
        ]);
    }

    /**
     * Generate a default description for the action.
     */
    protected function generateDescription(string $action, string $modelName): string
    {
        $identifier = $this->getHistoriqueIdentifier();
        
        return match($action) {
            'create' => "{$modelName} '{$identifier}' créé",
            'update' => "{$modelName} '{$identifier}' modifié",
            'delete' => "{$modelName} '{$identifier}' supprimé",
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

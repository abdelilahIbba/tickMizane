<?php

namespace App\Traits;

use App\Models\Historique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

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

        // Get old and new values for updates
        $oldValues = null;
        $newValues = null;
        
        if ($action === 'update') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getChanges();
            
            // Remove timestamps and hidden fields
            unset($oldValues['updated_at'], $oldValues['created_at'], $oldValues['password'], $oldValues['remember_token']);
            unset($newValues['updated_at'], $newValues['created_at'], $newValues['password'], $newValues['remember_token']);
        }

        Historique::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'system',
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $this->id,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'device_type' => $this->detectDeviceType(Request::userAgent()),
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

    /**
     * Detect device type from user agent.
     */
    protected function detectDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|android|blackberry|opera mini|opera mobi|skyfire|maemo|windows phone|palm|iemobile|symbian|symbianos|fennec)/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}


<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    use HasFactory, LogsHistorique;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'allowed',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'allowed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get historique identifier.
     */
    protected function getHistoriqueIdentifier(): string
    {
        return "Permission: {$this->module}.{$this->action}";
    }
}

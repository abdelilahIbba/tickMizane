<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    use HasFactory;

    protected $table = 'documentation';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'visible_to_roles',
        'order',
        'icon',
    ];

    protected $casts = [
        'visible_to_roles' => 'array',
        'order' => 'integer',
    ];

    public function scopeVisibleTo($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('visible_to_roles')
              ->orWhereJsonContains('visible_to_roles', $role);
        });
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}


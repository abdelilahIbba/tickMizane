<?php

namespace App\Models;

use App\Traits\LogsHistorique;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use LogsHistorique;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
    ];

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->key => $setting->castValue()])
            ->toArray();
    }

    /**
     * Get a single setting value
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->castValue() : $default;
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, mixed $value, ?string $group = null, ?string $type = null): static
    {
        $setting = static::firstOrNew(['key' => $key]);
        
        if ($group) {
            $setting->group = $group;
        }
        
        if ($type) {
            $setting->type = $type;
        }
        
        // Convert value based on type
        if ($setting->type === 'json' && is_array($value)) {
            $setting->value = json_encode($value);
        } elseif ($setting->type === 'boolean') {
            $setting->value = $value ? '1' : '0';
        } else {
            $setting->value = (string) $value;
        }
        
        $setting->save();
        
        return $setting;
    }

    /**
     * Cast value based on type
     */
    public function castValue(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Scope to filter by group
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}

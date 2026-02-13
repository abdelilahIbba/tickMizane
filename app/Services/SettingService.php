<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    protected int $cacheTtl = 3600;

    /**
     * Cache key prefix
     */
    protected string $cachePrefix = 'settings';

    /**
     * Default settings with their types and groups
     */
    protected array $defaults = [
        // General / Business
        'business_name' => ['value' => 'TechMizane Restaurant', 'type' => 'string', 'group' => 'general', 'label' => 'Nom de l\'établissement'],
        'business_address' => ['value' => '', 'type' => 'string', 'group' => 'general', 'label' => 'Adresse'],
        'business_phone' => ['value' => '', 'type' => 'string', 'group' => 'general', 'label' => 'Téléphone'],
        'business_email' => ['value' => '', 'type' => 'string', 'group' => 'general', 'label' => 'Email'],
        'tax_rate' => ['value' => '20', 'type' => 'float', 'group' => 'general', 'label' => 'Taux de TVA (%)'],
        'tax_number' => ['value' => '', 'type' => 'string', 'group' => 'general', 'label' => 'Numéro de TVA'],
        'currency_symbol' => ['value' => 'DH', 'type' => 'string', 'group' => 'general', 'label' => 'Symbole de devise'],
        'currency_position' => ['value' => 'after', 'type' => 'string', 'group' => 'general', 'label' => 'Position de la devise'],
        
        // Stock
        'stock_low_threshold_default' => ['value' => '10', 'type' => 'integer', 'group' => 'stock', 'label' => 'Seuil d\'alerte stock par défaut'],
        'stock_enable_notifications' => ['value' => '1', 'type' => 'boolean', 'group' => 'stock', 'label' => 'Activer les alertes de stock'],
        'stock_notification_email' => ['value' => '', 'type' => 'string', 'group' => 'stock', 'label' => 'Email pour les alertes stock'],
        'stock_auto_deduct' => ['value' => '1', 'type' => 'boolean', 'group' => 'stock', 'label' => 'Déduction automatique du stock'],
        
        // Payment
        'payment_cash_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Espèces activé'],
        'payment_card_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Carte bancaire activée'],
        'payment_mixed_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Paiement mixte activé'],
        'payment_default_method' => ['value' => 'cash', 'type' => 'string', 'group' => 'payment', 'label' => 'Méthode de paiement par défaut'],
        
        // Kitchen
        'kitchen_refresh_interval' => ['value' => '5', 'type' => 'integer', 'group' => 'kitchen', 'label' => 'Intervalle de rafraîchissement (secondes)'],
        'kitchen_warning_threshold' => ['value' => '15', 'type' => 'integer', 'group' => 'kitchen', 'label' => 'Seuil d\'avertissement (minutes)'],
        'kitchen_critical_threshold' => ['value' => '30', 'type' => 'integer', 'group' => 'kitchen', 'label' => 'Seuil critique (minutes)'],
        'kitchen_audio_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'kitchen', 'label' => 'Alertes audio activées'],
        'kitchen_voice_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'kitchen', 'label' => 'Annonces vocales activées'],
        'kitchen_auto_print_ticket' => ['value' => '0', 'type' => 'boolean', 'group' => 'kitchen', 'label' => 'Impression automatique des tickets'],
        
        // Receipts
        'receipt_header' => ['value' => '', 'type' => 'string', 'group' => 'receipts', 'label' => 'En-tête du ticket'],
        'receipt_footer' => ['value' => 'Merci de votre visite!', 'type' => 'string', 'group' => 'receipts', 'label' => 'Pied de page du ticket'],
        'receipt_show_tax' => ['value' => '1', 'type' => 'boolean', 'group' => 'receipts', 'label' => 'Afficher la TVA'],
        'receipt_paper_width' => ['value' => '80', 'type' => 'integer', 'group' => 'receipts', 'label' => 'Largeur du papier (mm)'],
        
        // Security
        'session_timeout' => ['value' => '30', 'type' => 'integer', 'group' => 'security', 'label' => 'Timeout de session (minutes)'],
        'max_login_attempts' => ['value' => '5', 'type' => 'integer', 'group' => 'security', 'label' => 'Tentatives de connexion max'],
        'lockout_duration' => ['value' => '15', 'type' => 'integer', 'group' => 'security', 'label' => 'Durée de verrouillage (minutes)'],
        'password_min_length' => ['value' => '8', 'type' => 'integer', 'group' => 'security', 'label' => 'Longueur minimale du mot de passe'],
        'password_require_special' => ['value' => '0', 'type' => 'boolean', 'group' => 'security', 'label' => 'Exiger caractères spéciaux'],
    ];

    /**
     * Get all settings organized by group
     */
    public function getAllByGroup(): array
    {
        return Cache::remember("{$this->cachePrefix}:all", $this->cacheTtl, function () {
            $settings = Setting::all();
            
            // Merge with defaults for any missing settings
            $result = [];
            foreach ($this->defaults as $key => $config) {
                $setting = $settings->firstWhere('key', $key);
                $group = $config['group'];
                
                if (!isset($result[$group])) {
                    $result[$group] = [];
                }
                
                $result[$group][$key] = [
                    'value' => $setting ? $setting->castValue() : $this->castDefaultValue($config),
                    'type' => $config['type'],
                    'label' => $config['label'] ?? $key,
                ];
            }
            
            return $result;
        });
    }

    /**
     * Get settings for a specific group
     */
    public function getGroup(string $group): array
    {
        return Cache::remember("{$this->cachePrefix}:{$group}", $this->cacheTtl, function () use ($group) {
            $settings = Setting::inGroup($group)->get();
            $result = [];
            
            foreach ($this->defaults as $key => $config) {
                if ($config['group'] !== $group) {
                    continue;
                }
                
                $setting = $settings->firstWhere('key', $key);
                $result[$key] = [
                    'value' => $setting ? $setting->castValue() : $this->castDefaultValue($config),
                    'type' => $config['type'],
                    'label' => $config['label'] ?? $key,
                    'description' => $config['description'] ?? null,
                ];
            }
            
            return $result;
        });
    }

    /**
     * Get a single setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cached = Cache::remember("{$this->cachePrefix}:key:{$key}", $this->cacheTtl, function () use ($key) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                return ['found' => true, 'value' => $setting->castValue()];
            }
            
            if (isset($this->defaults[$key])) {
                return ['found' => true, 'value' => $this->castDefaultValue($this->defaults[$key])];
            }
            
            return ['found' => false, 'value' => null];
        });
        
        return $cached['found'] ? $cached['value'] : $default;
    }

    /**
     * Set a setting value
     */
    public function set(string $key, mixed $value): Setting
    {
        $config = $this->defaults[$key] ?? ['type' => 'string', 'group' => 'general'];
        
        $setting = Setting::setValue($key, $value, $config['group'], $config['type']);
        
        // Clear relevant caches
        $this->clearCache($key, $config['group']);
        
        return $setting;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany(array $settings): void
    {
        $groups = [];
        
        foreach ($settings as $key => $value) {
            $config = $this->defaults[$key] ?? ['type' => 'string', 'group' => 'general'];
            Setting::setValue($key, $value, $config['group'], $config['type']);
            $groups[$config['group']] = true;
        }
        
        // Clear caches
        Cache::forget("{$this->cachePrefix}:all");
        foreach (array_keys($groups) as $group) {
            Cache::forget("{$this->cachePrefix}:{$group}");
        }
        foreach (array_keys($settings) as $key) {
            Cache::forget("{$this->cachePrefix}:key:{$key}");
        }
    }

    /**
     * Get available groups with their labels
     */
    public function getGroups(): array
    {
        return [
            'general' => [
                'label' => 'Général',
                'icon' => 'cog',
                'description' => 'Informations sur l\'établissement et paramètres généraux',
            ],
            'stock' => [
                'label' => 'Stock',
                'icon' => 'cube',
                'description' => 'Alertes et gestion du stock',
            ],
            'payment' => [
                'label' => 'Paiements',
                'icon' => 'credit-card',
                'description' => 'Méthodes de paiement acceptées',
            ],
            'kitchen' => [
                'label' => 'Cuisine',
                'icon' => 'fire',
                'description' => 'Paramètres de l\'affichage cuisine',
            ],
            'receipts' => [
                'label' => 'Tickets',
                'icon' => 'document-text',
                'description' => 'Configuration des tickets de caisse',
            ],
            'security' => [
                'label' => 'Sécurité',
                'icon' => 'shield-check',
                'description' => 'Paramètres de sécurité et sessions',
            ],
        ];
    }

    /**
     * Get defaults for a specific group (for seeding or reset)
     */
    public function getDefaultsForGroup(string $group): array
    {
        return array_filter($this->defaults, fn($config) => $config['group'] === $group);
    }

    /**
     * Reset a group to default values
     */
    public function resetGroup(string $group): void
    {
        $defaults = $this->getDefaultsForGroup($group);
        
        // Delete existing settings in this group
        Setting::inGroup($group)->delete();
        
        // Recreate with defaults
        foreach ($defaults as $key => $config) {
            Setting::create([
                'group' => $group,
                'key' => $key,
                'value' => $config['value'],
                'type' => $config['type'],
                'label' => $config['label'] ?? $key,
                'description' => $config['description'] ?? null,
            ]);
        }
        
        // Clear all caches
        $this->clearAllCache();
    }

    /**
     * Initialize all default settings (for seeder)
     */
    public function initializeDefaults(): void
    {
        foreach ($this->defaults as $key => $config) {
            $existing = Setting::where('key', $key)->first();
            
            if (!$existing) {
                Setting::create([
                    'group' => $config['group'],
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'label' => $config['label'] ?? $key,
                    'description' => $config['description'] ?? null,
                ]);
            }
        }
        
        $this->clearAllCache();
    }

    /**
     * Clear cache for a specific key
     */
    protected function clearCache(string $key, string $group): void
    {
        Cache::forget("{$this->cachePrefix}:all");
        Cache::forget("{$this->cachePrefix}:{$group}");
        Cache::forget("{$this->cachePrefix}:key:{$key}");
    }

    /**
     * Clear all settings cache
     */
    public function clearAllCache(): void
    {
        Cache::forget("{$this->cachePrefix}:all");
        foreach (array_keys($this->getGroups()) as $group) {
            Cache::forget("{$this->cachePrefix}:{$group}");
        }
        foreach (array_keys($this->defaults) as $key) {
            Cache::forget("{$this->cachePrefix}:key:{$key}");
        }
    }

    /**
     * Cast default value based on type
     */
    protected function castDefaultValue(array $config): mixed
    {
        return match ($config['type']) {
            'boolean' => (bool) $config['value'],
            'integer' => (int) $config['value'],
            'float' => (float) $config['value'],
            'json' => is_string($config['value']) ? json_decode($config['value'], true) : $config['value'],
            default => $config['value'],
        };
    }
}

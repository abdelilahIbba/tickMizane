<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class RestaurantSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General / Business ─────────────────────────────────────────
            ['group' => 'general',   'key' => 'business_name',    'value' => 'Restaurant Dar El Amal',              'type' => 'string',  'label' => 'Nom de l\'établissement'],
            ['group' => 'general',   'key' => 'business_address', 'value' => '12, Rue Moulay Ali Chérif, Guéliz, Marrakech 40000', 'type' => 'string', 'label' => 'Adresse'],
            ['group' => 'general',   'key' => 'business_phone',   'value' => '0524-447-892',                        'type' => 'string',  'label' => 'Téléphone'],
            ['group' => 'general',   'key' => 'business_email',   'value' => 'contact@dar-el-amal.ma',              'type' => 'string',  'label' => 'Email'],
            ['group' => 'general',   'key' => 'tax_rate',         'value' => '10',                                  'type' => 'float',   'label' => 'Taux de TVA (%)'],
            ['group' => 'general',   'key' => 'tax_number',       'value' => 'IF-45872361',                         'type' => 'string',  'label' => 'Identifiant fiscal'],
            ['group' => 'general',   'key' => 'currency_symbol',  'value' => 'DH',                                  'type' => 'string',  'label' => 'Symbole de devise'],
            ['group' => 'general',   'key' => 'currency_position','value' => 'after',                               'type' => 'string',  'label' => 'Position de la devise'],

            // ── Stock ──────────────────────────────────────────────────────
            ['group' => 'stock',     'key' => 'stock_low_threshold_default', 'value' => '10',  'type' => 'integer', 'label' => 'Seuil d\'alerte stock par défaut'],
            ['group' => 'stock',     'key' => 'stock_enable_notifications',  'value' => '1',   'type' => 'boolean', 'label' => 'Activer les alertes de stock'],
            ['group' => 'stock',     'key' => 'stock_notification_email',    'value' => 'stock@dar-el-amal.ma', 'type' => 'string', 'label' => 'Email pour les alertes stock'],
            ['group' => 'stock',     'key' => 'stock_auto_deduct',           'value' => '1',   'type' => 'boolean', 'label' => 'Déduction automatique du stock'],

            // ── Payment ────────────────────────────────────────────────────
            ['group' => 'payment',   'key' => 'payment_cash_enabled',   'value' => '1',     'type' => 'boolean', 'label' => 'Espèces activé'],
            ['group' => 'payment',   'key' => 'payment_card_enabled',   'value' => '1',     'type' => 'boolean', 'label' => 'Carte bancaire activée'],
            ['group' => 'payment',   'key' => 'payment_mixed_enabled',  'value' => '1',     'type' => 'boolean', 'label' => 'Paiement mixte activé'],
            ['group' => 'payment',   'key' => 'payment_default_method', 'value' => 'cash',  'type' => 'string',  'label' => 'Méthode de paiement par défaut'],

            // ── Kitchen ────────────────────────────────────────────────────
            ['group' => 'kitchen',   'key' => 'kitchen_refresh_interval',   'value' => '5',  'type' => 'integer', 'label' => 'Intervalle de rafraîchissement (secondes)'],
            ['group' => 'kitchen',   'key' => 'kitchen_warning_threshold',  'value' => '15', 'type' => 'integer', 'label' => 'Seuil d\'avertissement (minutes)'],
            ['group' => 'kitchen',   'key' => 'kitchen_critical_threshold', 'value' => '30', 'type' => 'integer', 'label' => 'Seuil critique (minutes)'],
            ['group' => 'kitchen',   'key' => 'kitchen_audio_enabled',      'value' => '1',  'type' => 'boolean', 'label' => 'Alertes audio activées'],
            ['group' => 'kitchen',   'key' => 'kitchen_voice_enabled',      'value' => '1',  'type' => 'boolean', 'label' => 'Annonces vocales activées'],
            ['group' => 'kitchen',   'key' => 'kitchen_auto_print_ticket',  'value' => '0',  'type' => 'boolean', 'label' => 'Impression automatique des tickets'],

            // ── Receipts ───────────────────────────────────────────────────
            ['group' => 'receipts',  'key' => 'receipt_header',      'value' => 'Restaurant Dar El Amal\n12, Rue Moulay Ali Chérif, Marrakech\nTél: 0524-447-892', 'type' => 'string', 'label' => 'En-tête du ticket'],
            ['group' => 'receipts',  'key' => 'receipt_footer',      'value' => 'شكراً لزيارتكم — Merci de votre visite !', 'type' => 'string', 'label' => 'Pied de page du ticket'],
            ['group' => 'receipts',  'key' => 'receipt_show_tax',    'value' => '1',  'type' => 'boolean', 'label' => 'Afficher la TVA'],
            ['group' => 'receipts',  'key' => 'receipt_paper_width', 'value' => '80', 'type' => 'integer', 'label' => 'Largeur du papier (mm)'],

            // ── Security ───────────────────────────────────────────────────
            ['group' => 'security',  'key' => 'session_timeout',        'value' => '30', 'type' => 'integer', 'label' => 'Timeout de session (minutes)'],
            ['group' => 'security',  'key' => 'max_login_attempts',     'value' => '5',  'type' => 'integer', 'label' => 'Tentatives de connexion max'],
            ['group' => 'security',  'key' => 'lockout_duration',       'value' => '15', 'type' => 'integer', 'label' => 'Durée de verrouillage (minutes)'],
            ['group' => 'security',  'key' => 'password_min_length',    'value' => '8',  'type' => 'integer', 'label' => 'Longueur minimale du mot de passe'],
            ['group' => 'security',  'key' => 'password_require_special','value' => '0', 'type' => 'boolean', 'label' => 'Exiger caractères spéciaux'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                $s
            );
        }

        $this->command->info('✔ RestaurantSettingsSeeder : ' . count($settings) . ' paramètres configurés.');
    }
}

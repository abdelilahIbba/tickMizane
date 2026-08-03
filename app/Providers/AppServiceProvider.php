<?php

namespace App\Providers;

use App\Auth\SuperAdminUserProvider;
use App\Models\Category;
use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Table;
use App\Models\Vente;
use App\Policies\CategoryPolicy;
use App\Policies\CommandePolicy;
use App\Policies\FournisseurPolicy;
use App\Policies\PaiementPolicy;
use App\Policies\ProductPolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\TablePolicy;
use App\Policies\VentePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Auth::provider('super_admin_eloquent', function ($app, array $config) {
            return new SuperAdminUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Produit::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Vente::class, VentePolicy::class);
        Gate::policy(Commande::class, CommandePolicy::class);
        Gate::policy(Fournisseur::class, FournisseurPolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
        Gate::policy(Paiement::class, PaiementPolicy::class);
        Gate::policy(Table::class, TablePolicy::class);

        $this->applyPublicBaseUrlOverride();
    }

    /**
     * Force a public base URL from settings when available.
     */
    private function applyPublicBaseUrlOverride(): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            $publicBaseUrl = Setting::where('key', 'public_base_url')->value('value');
            if (!is_string($publicBaseUrl) || trim($publicBaseUrl) === '') {
                return;
            }

            $publicBaseUrl = rtrim($publicBaseUrl, '/');

            // Only force the configured public URL in console contexts
            // (queues, notifications, CLI URL generation).
            // For normal HTTP requests we keep the current host so local/dev
            // assets do not break when the configured IP is temporarily unreachable.
            if ($this->app->runningInConsole()) {
                URL::forceRootUrl($publicBaseUrl);
                config(['app.url' => $publicBaseUrl]);

                if (str_starts_with($publicBaseUrl, 'https://')) {
                    URL::forceScheme('https');
                }
            }
        } catch (Throwable) {
            // Ignore boot-time URL override failures (e.g. during initial migration).
        }
    }
}

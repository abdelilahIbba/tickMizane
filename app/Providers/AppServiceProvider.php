<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Paiement;
use App\Models\Produit;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}

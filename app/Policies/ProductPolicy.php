<?php

namespace App\Policies;

use App\Models\Produit;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        // Admin and caissier can view products
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Produit $produit): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Produit $produit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Produit $produit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update stock.
     */
    public function updateStock(User $user, Produit $produit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Produit $produit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the product.
     */
    public function forceDelete(User $user, Produit $produit): bool
    {
        return $user->isAdmin();
    }
}

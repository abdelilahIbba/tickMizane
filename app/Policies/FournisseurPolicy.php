<?php

namespace App\Policies;

use App\Models\Fournisseur;
use App\Models\User;

class FournisseurPolicy
{
    /**
     * Determine whether the user can view any fournisseurs.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the fournisseur.
     */
    public function view(User $user, Fournisseur $fournisseur): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create fournisseurs.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the fournisseur.
     */
    public function update(User $user, Fournisseur $fournisseur): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the fournisseur.
     */
    public function delete(User $user, Fournisseur $fournisseur): bool
    {
        // Can only delete if no orders associated
        return $user->isAdmin() && !$fournisseur->commandes()->exists();
    }

    /**
     * Determine whether the user can restore the fournisseur.
     */
    public function restore(User $user, Fournisseur $fournisseur): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the fournisseur.
     */
    public function forceDelete(User $user, Fournisseur $fournisseur): bool
    {
        return $user->isAdmin();
    }
}

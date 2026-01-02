<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    /**
     * Determine whether the user can view any commandes.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the commande.
     */
    public function view(User $user, Commande $commande): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create commandes.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the commande.
     */
    public function update(User $user, Commande $commande): bool
    {
        // Can only update pending orders
        return $user->isAdmin() && $commande->isPending();
    }

    /**
     * Determine whether the user can mark the commande as received.
     */
    public function receive(User $user, Commande $commande): bool
    {
        return $user->isAdmin() && $commande->isPending();
    }

    /**
     * Determine whether the user can delete the commande.
     */
    public function delete(User $user, Commande $commande): bool
    {
        // Can only delete pending orders
        return $user->isAdmin() && $commande->isPending();
    }

    /**
     * Determine whether the user can restore the commande.
     */
    public function restore(User $user, Commande $commande): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the commande.
     */
    public function forceDelete(User $user, Commande $commande): bool
    {
        return $user->isAdmin();
    }
}

<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewCommandes($user);
    }

    public function view(User $user, Commande $commande): bool
    {
        return $this->canViewCommandes($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isServeur();
    }

    public function update(User $user, Commande $commande): bool
    {
        return $user->isAdmin() && $commande->isOpenForEdit();
    }

    public function receive(User $user, Commande $commande): bool
    {
        return false;
    }

    public function delete(User $user, Commande $commande): bool
    {
        return $user->isAdmin() && $commande->isOpenForEdit();
    }

    public function restore(User $user, Commande $commande): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Commande $commande): bool
    {
        return $user->isAdmin();
    }

    private function canViewCommandes(User $user): bool
    {
        return $user->isAdmin() || $user->isCaissier() || $user->isServeur();
    }
}

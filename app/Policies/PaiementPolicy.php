<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    /**
     * Determine whether the user can view any paiements.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can view the paiement.
     */
    public function view(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can create paiements.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can update the paiement.
     */
    public function update(User $user, Paiement $paiement): bool
    {
        // Only admin can update payments
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can refund the paiement.
     */
    public function refund(User $user, Paiement $paiement): bool
    {
        // Only admin can refund
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the paiement.
     */
    public function delete(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can view receipt.
     */
    public function viewReceipt(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }
}

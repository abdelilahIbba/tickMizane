<?php

namespace App\Policies;

use App\Models\Vente;
use App\Models\User;

class VentePolicy
{
    /**
     * Determine whether the user can view any ventes.
     */
    public function viewAny(User $user): bool
    {
        // Admin and caissier can view ventes
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can view the vente.
     */
    public function view(User $user, Vente $vente): bool
    {
        // Admin can view any vente
        if ($user->isAdmin()) {
            return true;
        }

        // Caissier can view their own ventes
        if ($user->isCaissier()) {
            return $vente->user_id === $user->id || true; // Or allow all for now
        }

        return false;
    }

    /**
     * Determine whether the user can create ventes (via POS).
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can update the vente.
     */
    public function update(User $user, Vente $vente): bool
    {
        // Only admin can update ventes
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can cancel the vente.
     */
    public function cancel(User $user, Vente $vente): bool
    {
        // Only admin can cancel ventes
        // Or caissier can cancel their own ventes within same day
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCaissier() && $vente->user_id === $user->id) {
            return $vente->created_at->isToday();
        }

        return false;
    }

    /**
     * Determine whether the user can add payments.
     */
    public function addPayment(User $user, Vente $vente): bool
    {
        return $user->isAdmin() || $user->isCaissier();
    }

    /**
     * Determine whether the user can delete the vente.
     */
    public function delete(User $user, Vente $vente): bool
    {
        // Only admin can delete ventes
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->isAdmin();
    }
}

<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy
{
    /**
     * Determine whether the user can view any stock movements.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the stock movement.
     */
    public function view(User $user, StockMovement $stockMovement): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create stock movements.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the stock movement.
     */
    public function update(User $user, StockMovement $stockMovement): bool
    {
        // Can only update manual adjustments, not auto-generated movements
        return $user->isAdmin() && in_array($stockMovement->reason, ['ajustement', 'perte']);
    }

    /**
     * Determine whether the user can delete the stock movement.
     */
    public function delete(User $user, StockMovement $stockMovement): bool
    {
        // Can only delete manual adjustments
        return $user->isAdmin() && in_array($stockMovement->reason, ['ajustement', 'perte']);
    }

    /**
     * Determine whether the user can restore the stock movement.
     */
    public function restore(User $user, StockMovement $stockMovement): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the stock movement.
     */
    public function forceDelete(User $user, StockMovement $stockMovement): bool
    {
        return $user->isAdmin();
    }
}

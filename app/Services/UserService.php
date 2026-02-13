<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class UserService
{
    /**
     * Get all users.
     */
    public function getAllUsers(): Collection
    {
        return User::orderBy('name')->get();
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $role): Collection
    {
        return User::where('role', $role)->orderBy('name')->get();
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => $data['status'] ?? 'active',
                'force_password_reset' => $data['force_password_reset'] ?? false,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Update user details.
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }

            if (isset($data['username'])) {
                $user->username = $data['username'];
            }

            if (isset($data['role'])) {
                $user->role = $data['role'];
            }

            if (isset($data['status'])) {
                $user->status = $data['status'];
            }

            $user->save();

            return $user->fresh();
        });
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user, ?string $newPassword = null, bool $forceReset = true): string
    {
        return DB::transaction(function () use ($user, $newPassword, $forceReset) {
            // Generate random password if not provided
            $tempPassword = $newPassword ?? Str::random(12);

            $user->update([
                'password' => Hash::make($tempPassword),
                'force_password_reset' => $forceReset,
            ]);

            return $tempPassword;
        });
    }

    /**
     * Change user password (by user themselves).
     */
    public function changePassword(User $user, string $newPassword): void
    {
        DB::transaction(function () use ($user, $newPassword) {
            $user->update([
                'password' => Hash::make($newPassword),
                'force_password_reset' => false,
            ]);
        });
    }

    /**
     * Activate user account.
     */
    public function activateUser(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->update(['status' => 'active']);

            return $user->fresh();
        });
    }

    /**
     * Deactivate user account.
     */
    public function deactivateUser(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->update(['status' => 'blocked']);

            return $user->fresh();
        });
    }

    /**
     * Delete user (soft).
     */
    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Deactivate instead of hard delete
            $user->update(['status' => 'blocked']);

            return true;
        });
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * Check if user needs to reset password.
     */
    public function needsPasswordReset(User $user): bool
    {
        return $user->force_password_reset;
    }
}

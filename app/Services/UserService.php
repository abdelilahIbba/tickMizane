<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * UserService - خدمة إدارة المستخدمين
 *
 * تتولى عمليات إنشاء وتعديل وحذف
 * وإدارة حسابات المستخدمين في النظام.
 *
 * الوظائف الرئيسية:
 * - إنشاء مستخدم بكلمة مرور مؤقتة عشوائية
 * - إعادة تعيين كلمة المرور مع إرسال إشعار (لم يُفعَّل بعد)
 * - تفعيل/تعطيل وحظر الحسابات
 * - إحصاءيات نشاط المستخدمين
 */
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
            // Password is hashed by the User model cast — do not Hash::make here.
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role'],
                'status' => $data['status'] ?? 'active',
                'force_password_reset' => (bool) ($data['force_password_reset'] ?? false),
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
            $tempPassword = $newPassword ?? Str::random(12);

            $user->update([
                'password' => $tempPassword,
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
                'password' => $newPassword,
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
     * Permanently delete a user when safe.
     *
     * Users linked to ventes/commandes cannot be hard-deleted (would cascade
     * wipe financial history). Deactivate them instead.
     */
    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            if ($user->ventes()->exists() || $user->commandes()->exists()) {
                throw new RuntimeException(
                    'Impossible de supprimer cet utilisateur car il est lié à des ventes ou commandes. Désactivez-le à la place.'
                );
            }

            $user->permissions()->delete();
            $user->tokens()->delete();
            $user->delete();

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

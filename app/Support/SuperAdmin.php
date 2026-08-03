<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    public const ROLE = 'super_admin';

    public static function enabled(): bool
    {
        return (bool) config('super_admin.enabled', true);
    }

    public static function id(): int
    {
        return (int) config('super_admin.id', 0);
    }

    public static function username(): string
    {
        return (string) config('super_admin.username', 'devnapp');
    }

    public static function pin(): string
    {
        return (string) config('super_admin.pin', '009988');
    }

    public static function matchesPin(string $pin): bool
    {
        return self::enabled() && hash_equals(self::pin(), $pin);
    }

    public static function is(mixed $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        return $user->role === self::ROLE
            || (int) $user->getAuthIdentifier() === self::id()
            || $user->username === self::username();
    }

    /**
     * User id safe for foreign keys.
     * Super Admin is synthetic (id 0) and must never be written to DB FKs.
     */
    public static function databaseUserId(?User $user = null): ?int
    {
        $user ??= Auth::user();

        if (!$user || self::is($user)) {
            return null;
        }

        $id = $user->getAuthIdentifier();

        return is_numeric($id) && (int) $id > 0 ? (int) $id : null;
    }

    public static function make(): User
    {
        $user = new User([
            'name' => (string) config('super_admin.name', 'DevNApp Super Admin'),
            'username' => self::username(),
            'role' => self::ROLE,
            'status' => 'active',
            'force_password_reset' => false,
            'last_login_at' => null,
        ]);

        $user->id = self::id();
        $user->exists = false;
        $user->syncOriginal();

        return $user;
    }
}

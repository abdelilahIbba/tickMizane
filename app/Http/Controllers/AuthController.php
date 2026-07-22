<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin PIN code (password-only login)
     */
    private const ADMIN_PIN = '009988';

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     * Single PIN login for all active roles.
     */
    public function login(Request $request)
    {
        return $this->pinLogin($request);
    }

    private function pinLogin(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $pin = (string) $request->password;

        // Bootstrap admin support: if no admin exists, keep legacy bootstrap PIN behavior.
        $admin = User::where('role', 'admin')
            ->where('status', 'active')
            ->select(['id', 'name', 'username', 'password', 'role', 'status', 'force_password_reset'])
            ->first();

        if (!$admin && $pin === self::ADMIN_PIN) {
            $admin = $this->createBootstrapAdmin();
            Auth::login($admin);
            $request->session()->regenerate();
            $admin->forceFill(['last_login_at' => now()])->saveQuietly();

            return $this->redirectByRole($admin);
        }

        $activeUsers = User::where('status', 'active')
            ->whereIn('role', ['admin', 'caissier', 'serveur'])
            ->select(['id', 'name', 'username', 'password', 'role', 'status', 'force_password_reset'])
            ->get();

        $matchingUsers = $activeUsers->filter(function (User $user) use ($pin): bool {
            if ($user->role === 'admin' && $pin === self::ADMIN_PIN) {
                return true;
            }

            return Hash::check($pin, $user->password);
        })->values();

        if ($matchingUsers->count() > 1) {
            throw ValidationException::withMessages([
                'password' => 'Code PIN ambigu. Contactez l\'administrateur pour utiliser un code unique.',
            ]);
        }

        $user = $matchingUsers->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'password' => 'Code PIN incorrect.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return $this->redirectByRole($user);
    }

    /**
     * Create a default active admin when none exists.
     */
    private function createBootstrapAdmin(): User
    {
        $baseUsername = 'admin';
        $username = $baseUsername;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $suffix++;
            $username = $baseUsername . $suffix;
        }

        return User::create([
            'name' => 'Administrateur',
            'username' => $username,
            'password' => Hash::make(self::ADMIN_PIN),
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('dashboard'),
            'caissier' => redirect()->route('kitchen.index'),
            'serveur' => redirect()->route('waiter.index'),
            default => redirect()->route('dashboard'),
        };
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show password change form.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password (skip if force_password_reset is true and it's a temp password)
        if (!$user->force_password_reset && !Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_reset' => false,
        ]);

        // Log the action
        \App\Models\Historique::create([
            'action' => 'password_changed',
            'table_name' => 'users',
            'record_id' => $user->id,
            'user_id' => $user->id,
            'description' => 'User changed their password',
        ]);

        return $this->redirectByRole($user)->with('success', 'Mot de passe changé avec succès.');
    }
}

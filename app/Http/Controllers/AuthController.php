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
     * - Admin: password-only login (PIN: 009988)
     * - Caissier/Serveur: username + password login
     */
    public function login(Request $request)
    {
        $loginMode = $request->input('login_mode', 'staff');

        if ($loginMode === 'admin') {
            return $this->adminLogin($request);
        }

        return $this->staffLogin($request);
    }

    /**
     * Admin login - password/PIN only (009988)
     */
    private function adminLogin(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Find admin user and verify PIN
        $admin = User::where('role', 'admin')
                     ->where('status', 'active')
                     ->select(['id', 'name', 'username', 'password', 'role', 'status', 'force_password_reset'])
                     ->first();

        if (!$admin) {
            if ($request->password !== self::ADMIN_PIN) {
                throw ValidationException::withMessages([
                    'password' => 'Aucun administrateur trouvé.',
                ]);
            }

            $admin = $this->createBootstrapAdmin();
        }

        // Check if password matches the admin PIN
        if ($request->password === self::ADMIN_PIN || Hash::check($request->password, $admin->password)) {
            Auth::login($admin);
            $request->session()->regenerate();
            $admin->forceFill(['last_login_at' => now()])->saveQuietly();
            return $this->redirectByRole($admin);
        }

        throw ValidationException::withMessages([
            'password' => 'Code PIN incorrect.',
        ]);
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
     * Staff login - username + password
     */
    private function staffLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username (non-admin)
        $user = User::where('username', $request->username)
                    ->where('status', 'active')
                    ->whereIn('role', ['caissier', 'serveur'])
                    ->select(['id', 'name', 'username', 'password', 'role', 'status', 'force_password_reset'])
                    ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => 'Utilisateur non trouvé.',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Mot de passe incorrect.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        return $this->redirectByRole($user);
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('dashboard'),
            'caissier' => redirect()->route('pos.index'),
            'serveur' => redirect()->route('tables.index'),
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

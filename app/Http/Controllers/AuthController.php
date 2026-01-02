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
                     ->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'password' => 'Aucun administrateur trouvé.',
            ]);
        }

        // Check if password matches the admin PIN
        if ($request->password === self::ADMIN_PIN || Hash::check($request->password, $admin->password)) {
            Auth::login($admin);
            $request->session()->regenerate();
            return $this->redirectByRole($admin);
        }

        throw ValidationException::withMessages([
            'password' => 'Code PIN incorrect.',
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
}

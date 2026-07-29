<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LicenseService;
use App\Support\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {
    }

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

        if (SuperAdmin::matchesPin($pin)) {
            $superAdmin = SuperAdmin::make();
            Auth::login($superAdmin);
            $request->session()->regenerate();

            return redirect()->route('settings.licenses.index');
        }

        $activeUsers = User::where('status', 'active')
            ->whereIn('role', ['admin', 'caissier', 'serveur'])
            ->select(['id', 'name', 'username', 'password', 'role', 'status', 'force_password_reset'])
            ->get();

        $matchingUsers = $activeUsers->filter(function (User $user) use ($pin): bool {
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

        if ($this->licenseService->isExpiredOrMissing()) {
            throw ValidationException::withMessages([
                'password' => $this->licenseService->clientBlockMessage(),
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
        if (SuperAdmin::is($user)) {
            return redirect()->route('settings.licenses.index');
        }

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
        if (SuperAdmin::is(Auth::user())) {
            return redirect()->route('settings.licenses.index')
                ->with('error', 'Le Super Admin n\'utilise pas de mot de passe utilisateur.');
        }

        return view('auth.change-password');
    }

    /**
     * Handle password change.
     */
    public function changePassword(Request $request)
    {
        if (SuperAdmin::is(Auth::user())) {
            abort(403, 'Le Super Admin ne peut pas changer de mot de passe utilisateur.');
        }

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

        // Update password — hashed cast will hash once
        $user->update([
            'password' => $request->password,
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

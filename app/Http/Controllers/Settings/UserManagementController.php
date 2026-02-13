<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    protected UserService $userService;
    protected PermissionService $permissionService;

    public function __construct(UserService $userService, PermissionService $permissionService)
    {
        $this->userService = $userService;
        $this->permissionService = $permissionService;
    }

    /**
     * Display list of users.
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();
        
        return view('settings.users.index', compact('users'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        return view('settings.users.create');
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,caissier,serveur',
            'status' => 'required|in:active,blocked',
            'force_password_reset' => 'boolean',
        ]);

        try {
            $user = $this->userService->createUser($validated);

            // Initialize default permissions based on role
            $this->permissionService->initializeDefaultPermissions($user);

            return redirect()
                ->route('settings.users.index')
                ->with('success', "Utilisateur {$user->name} créé avec succès");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        return view('settings.users.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,caissier,serveur',
            'status' => 'required|in:active,blocked',
        ]);

        try {
            $this->userService->updateUser($user, $validated);

            return redirect()
                ->route('settings.users.index')
                ->with('success', "Utilisateur {$user->name} mis à jour");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Show password reset form.
     */
    public function showResetPassword(User $user)
    {
        return view('settings.users.reset-password', compact('user'));
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => ['nullable', 'confirmed', Password::min(8)],
            'force_reset' => 'boolean',
        ]);

        try {
            $tempPassword = $this->userService->resetPassword(
                $user,
                $validated['new_password'] ?? null,
                $validated['force_reset'] ?? true
            );

            $message = $validated['new_password'] 
                ? "Mot de passe réinitialisé"
                : "Mot de passe temporaire généré: {$tempPassword}";

            return redirect()
                ->route('settings.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Activate user.
     */
    public function activate(User $user)
    {
        try {
            $this->userService->activateUser($user);

            return redirect()
                ->route('settings.users.index')
                ->with('success', "Utilisateur {$user->name} activé");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate user.
     */
    public function deactivate(User $user)
    {
        try {
            // Prevent self-deactivation
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte');
            }

            $this->userService->deactivateUser($user);

            return redirect()
                ->route('settings.users.index')
                ->with('success', "Utilisateur {$user->name} désactivé");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
            }

            $userName = $user->name;
            $this->userService->deleteUser($user);

            return redirect()
                ->route('settings.users.index')
                ->with('success', "Utilisateur {$userName} supprimé");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}


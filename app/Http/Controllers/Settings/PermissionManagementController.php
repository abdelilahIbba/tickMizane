<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionManagementController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display permissions management page.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        $modules = PermissionService::MODULES;
        $actions = PermissionService::ACTIONS;

        return view('settings.permissions.index', compact('users', 'modules', 'actions'));
    }

    /**
     * Show user permissions.
     */
    public function show(User $user)
    {
        $permissionMatrix = $this->permissionService->getPermissionMatrix($user);
        $modules = PermissionService::MODULES;
        $actions = PermissionService::ACTIONS;

        return view('settings.permissions.show', compact('user', 'permissionMatrix', 'modules', 'actions'));
    }

    /**
     * Update user permissions.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
        ]);

        try {
            $this->permissionService->setPermissions($user, $validated['permissions']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissions mises à jour',
                ]);
            }

            return redirect()
                ->route('settings.permissions.show', $user)
                ->with('success', 'Permissions mises à jour');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Grant all permissions to user.
     */
    public function grantAll(User $user)
    {
        try {
            $this->permissionService->grantAllPermissions($user);

            return redirect()
                ->route('settings.permissions.show', $user)
                ->with('success', 'Toutes les permissions accordées');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Revoke all permissions from user.
     */
    public function revokeAll(User $user)
    {
        try {
            $this->permissionService->revokeAllPermissions($user);

            return redirect()
                ->route('settings.permissions.show', $user)
                ->with('success', 'Toutes les permissions révoquées');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}


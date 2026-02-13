<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Non authentifié');
        }

        if (!$user->isActive()) {
            abort(403, 'Compte désactivé');
        }

        // Check permission
        if (!$this->permissionService->hasPermission($user, $module, $action)) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}


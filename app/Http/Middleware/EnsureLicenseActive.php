<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use App\Support\SuperAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseActive
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * Routes that remain reachable without an active license.
     */
    protected array $except = [
        'login',
        'login.submit',
        'logout',
        'password.change',
        'password.change.submit',
        'settings.licenses.index',
        'settings.licenses.store',
        'settings.licenses.activate',
        'settings.licenses.revoke',
        'license.blocked',
    ];

    /**
     * API paths that remain reachable without an active license.
     */
    protected array $exceptApiPaths = [
        'api/v1/auth/login',
        'api/v1/auth/logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $this->licenseService->markExpiredLicenses();

        $user = $request->user();

        if (SuperAdmin::is($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $this->except, true)) {
            return $next($request);
        }

        if (in_array($request->path(), $this->exceptApiPaths, true)) {
            return $next($request);
        }

        if ($this->licenseService->hasActiveLicense()) {
            return $next($request);
        }

        $message = $this->licenseService->clientBlockMessage();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'code' => 'license_inactive',
                'message' => $message,
            ], 403);
        }

        if ($user) {
            return redirect()->route('license.blocked');
        }

        return redirect()->route('login')->with('error', $message);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    /**
     * Routes that should be excluded from password reset check
     */
    protected array $except = [
        'logout',
        'password.change',
        'password.change.submit',
        'settings.users.change-password',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for guests
        if (!$user) {
            return $next($request);
        }

        // Skip for excluded routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $this->except)) {
            return $next($request);
        }

        // Check if user needs to reset password
        if ($user->force_password_reset) {
            // For AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez changer votre mot de passe avant de continuer.',
                    'redirect' => route('password.change'),
                ], 403);
            }

            // Flash message and redirect to password change page
            return redirect()->route('password.change')
                ->with('warning', 'Vous devez changer votre mot de passe avant de continuer.');
        }

        return $next($request);
    }
}

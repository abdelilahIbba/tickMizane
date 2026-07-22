<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if user has the required role(s) to access the route.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (e.g., 'admin', 'caissier', 'serveur')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        $user = $request->user();

        // Check if user is active (blocked users should be logged out)
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte a été bloqué. Contactez l\'administrateur.'
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Votre compte a été bloqué. Contactez l\'administrateur.');
        }

        $userRole = $user->role;

        // Admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user's role is in the allowed roles
        if (!in_array($userRole, $roles)) {
            // Redirect to appropriate page based on role
            return $this->unauthorizedRedirect($request, $userRole);
        }

        return $next($request);
    }

    /**
     * Redirect unauthorized user to their appropriate page.
     */
    private function unauthorizedRedirect(Request $request, string $role): Response
    {
        $message = 'Vous n\'avez pas les droits pour accéder à cette page.';

        // If it's an AJAX request, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        // Redirect to role-appropriate page
        $redirectRoute = match ($role) {
            'caissier' => 'kitchen.index',
            'serveur' => 'waiter.index',
            default => 'login',
        };

        return redirect()->route($redirectRoute)->with('error', $message);
    }
}


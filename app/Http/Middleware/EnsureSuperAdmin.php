<?php

namespace App\Http\Middleware;

use App\Support\SuperAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!SuperAdmin::is($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé au Super Admin.',
                ], 403);
            }

            abort(403, 'Accès réservé au Super Admin.');
        }

        return $next($request);
    }
}

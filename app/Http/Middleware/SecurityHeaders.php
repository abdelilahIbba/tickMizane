<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply security response headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Control referrer information sent
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Enforce HTTPS Strict Transport Security (HSTS)
        if ($request->isSecure() || config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Restrict browser features / APIs
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; " .
               "img-src 'self' data: blob: https:; " .
               "connect-src 'self' wss: https:; " .
               "frame-ancestors 'self'; " .
               "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}

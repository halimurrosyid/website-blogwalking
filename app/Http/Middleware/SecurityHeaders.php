<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply security HTTP headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking Defense
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-Sniffing Defense
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cross-Site Scripting (XSS) Legacy Defense
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Privacy Protection
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Hardware & Browser Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Remove revealing server headers if present
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}

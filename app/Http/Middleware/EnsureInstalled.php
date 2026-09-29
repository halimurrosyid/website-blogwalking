<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Handle an incoming request and ensure application is installed.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass installer checks during automated testing unless explicitly enabled for testing
        if (app()->environment('testing') && ! config('app.enforce_installed_middleware', false)) {
            return $next($request);
        }

        $isInstalled = file_exists(storage_path('installed'));
        $isInstallRoute = $request->routeIs('install.*') || $request->is('install*');

        if (! $isInstalled) {
            // Not installed yet -> must proceed through installer
            if (! $isInstallRoute) {
                return redirect()->route('install.index');
            }
        } else {
            // Already installed -> block access to installer for security
            if ($isInstallRoute) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}

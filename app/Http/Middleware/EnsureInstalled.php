<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

            // Self-healing database: Automatically migrate pending migrations when needed (Hostinger/cPanel friendly)
            $this->ensureDatabaseUpToDate();
        }

        return $next($request);
    }

    /**
     * Ensure database tables and columns are up to date automatically
     * without requiring SSH terminal on shared hosting (e.g. Hostinger).
     */
    protected function ensureDatabaseUpToDate(): void
    {
        $markerFile = storage_path('framework/schema_v4.migrated');

        if (file_exists($markerFile)) {
            return;
        }

        try {
            // Check if essential tables or columns exist; if any are missing, run migrate
            if (! Schema::hasTable('periods') ||
                ! Schema::hasTable('app_settings') ||
                ! Schema::hasColumn('domains', 'ip_subnet') ||
                ! Schema::hasColumn('submissions', 'period_id') ||
                ! Schema::hasColumn('submissions', 'task_type') ||
                ! Schema::hasColumn('target_urls', 'task_type')) {
                Artisan::call('migrate', ['--force' => true]);
            }

            if (! file_exists(public_path('storage'))) {
                try {
                    Artisan::call('storage:link');
                } catch (\Throwable $linkEx) {
                    // Handled gracefully by storage.fallback route
                }
            }

            @file_put_contents($markerFile, now()->toIso8601String());
        } catch (\Throwable $e) {
            Log::warning('Automatic database migration failed or skipped: '.$e->getMessage());
        }
    }
}

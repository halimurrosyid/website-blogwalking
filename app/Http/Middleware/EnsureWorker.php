<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorker
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan oleh administrator.');
        }

        if (! $request->user()->isBlogwalker() && ! $request->user()->isAdmin()) {
            return redirect()->route('login')->with('error', 'Akses hanya untuk akun Blogwalker.');
        }

        return $next($request);
    }
}

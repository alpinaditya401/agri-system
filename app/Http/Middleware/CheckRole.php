<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Supports comma-separated roles: middleware('role:farmer,distributor')
     * means the user must have one of those roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role?->name;

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Akses ditolak. Peran Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}

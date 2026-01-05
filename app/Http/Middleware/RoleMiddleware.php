<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        foreach ($roles as $role) {
            // 'admin' role in middleware matches both admin and registrar
            // This allows shared routes for both roles
            if ($role === 'admin' && in_array($userRole, ['admin', 'registrar'])) {
                return $next($request);
            }

            // 'superadmin' middleware alias for backward compatibility
            if ($role === 'superadmin' && $userRole === 'admin') {
                return $next($request);
            }

            // Direct role match
            if ($userRole === $role) {
                return $next($request);
            }
        }

        // User doesn't have the required role
        abort(403, 'Unauthorized action.');
    }
}


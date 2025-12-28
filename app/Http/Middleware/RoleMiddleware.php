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
            // Handle 'admin' as a special role that matches both superadmin and registrar
            if ($role === 'admin' && in_array($userRole, ['superadmin', 'registrar'])) {
                return $next($request);
            }

            if ($userRole === $role) {
                return $next($request);
            }
        }

        // User doesn't have the required role
        abort(403, 'Unauthorized action.');
    }
}


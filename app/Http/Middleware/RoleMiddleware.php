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
            return redirect()->route("login");
        }

        $userRole = $request->user()->role;

        foreach ($roles as $role) {
            // Direct role match
            if ($userRole === $role) {
                return $next($request);
            }
            
            // Allow Admin to access Registrar routes if needed (optional, but good for hierarchy)
            // If the route requires "registrar", and the user is "admin", allow it.
            if ($role === "registrar" && $userRole === "admin") {
                return $next($request);
            }
        }

        // User doesn"t have the required role
        abort(403, "Unauthorized action.");
    }
}

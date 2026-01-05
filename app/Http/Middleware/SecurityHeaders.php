<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply security headers in production
        if (app()->isProduction()) {
            $securityConfig = config('security.headers', []);

            foreach ($securityConfig as $header => $value) {
                $response->header($header, $value);
            }
        }

        return $response;
    }
}

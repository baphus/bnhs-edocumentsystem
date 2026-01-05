<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    /**
     * Handle rate limiting based on configuration.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.rate_limiting.enabled')) {
            return $next($request);
        }

        // Rate limiting is handled by Laravel's built-in throttle middleware
        // This middleware can be extended for custom rate limiting logic

        return $next($request);
    }
}

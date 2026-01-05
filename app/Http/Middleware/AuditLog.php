<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLog
{
    /**
     * Log important actions for audit trail.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log sensitive operations
        if ($this->shouldAudit($request)) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be audited.
     */
    protected function shouldAudit(Request $request): bool
    {
        // Log POST, PUT, DELETE, PATCH requests
        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return false;
        }

        // Don't log login/logout if you prefer
        $excludePaths = [
            'api/auth/logout',
        ];

        foreach ($excludePaths as $path) {
            if ($request->is($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log the audit trail.
     */
    protected function logAudit(Request $request, Response $response): void
    {
        Log::channel('audit')->info('Action performed', [
            'user_id' => auth()->id(),
            'email' => auth()->user()?->email ?? 'anonymous',
            'method' => $request->method(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->status(),
        ]);
    }
}

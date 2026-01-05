<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - applied to all requests
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        // Web middleware stack
        $middleware->web(append: [
            // Security middleware
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ValidateInput::class,
            \App\Http\Middleware\AuditLog::class,
            // Inertia and asset middleware
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'security' => \App\Http\Middleware\SecurityHeaders::class,
            'audit' => \App\Http\Middleware\AuditLog::class,
        ]);

        // Rate limiting
        $middleware->throttle('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

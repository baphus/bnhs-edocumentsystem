<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserActivitySubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void
    {
        $user = $event->user;
        AuditLogService::log(
            'LOGIN',
            "User {$user->email} logged in",
            $user
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void
    {
        if ($event->user) {
            $user = $event->user;
            AuditLogService::log(
                'LOGOUT',
                "User {$user->email} logged out",
                $user
            );
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailedLogin(Failed $event): void
    {
        // For failed logins, we might not have a user object if the user doesn't exist
        // But $event->user might be populated if the user exists but password was wrong.
        // Usually we want to log the meaningful info.
        
        $email = isset($event->credentials['email']) ? $event->credentials['email'] : 'unknown';
        
        AuditLogService::log(
            'LOGIN_FAILED',
            "Failed login attempt for {$email}",
            $event->user // Might be null
        );
    }

    /**
     * Subscribe to the events.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Logout::class => 'handleUserLogout',
            Failed::class => 'handleFailedLogin',
        ];
    }
}

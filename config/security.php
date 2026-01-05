<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS & Security Headers Configuration
    |--------------------------------------------------------------------------
    */

    'cors' => [
        'paths' => ['api/*', 'sanctum/csrf-cookie'],
        'allowed_methods' => ['*'],
        'allowed_origins' => [
            // Production: Set specific domains
            env('APP_URL', 'http://localhost'),
        ],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => ['X-Total-Count', 'X-Page-Count'],
        'max_age' => 3600,
        'supports_credentials' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers (HSTS, CSP, etc.)
    |--------------------------------------------------------------------------
    */

    'headers' => [
        // Strict-Transport-Security: Enforce HTTPS for 1 year
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',

        // X-Content-Type-Options: Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',

        // X-Frame-Options: Clickjacking protection
        'X-Frame-Options' => 'DENY',

        // X-XSS-Protection: XSS attack prevention
        'X-XSS-Protection' => '1; mode=block',

        // Referrer-Policy: Control referrer information
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Permissions-Policy: Control browser features
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',

        // Content-Security-Policy (CSP): Prevent XSS and injection attacks
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' fonts.gstatic.com; connect-src 'self' https://api.example.com; frame-ancestors 'none'",
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),

        // Login attempts: 5 per 15 minutes
        'login_attempts' => env('RATE_LIMIT_LOGIN_ATTEMPT', '5,15'),

        // OTP sending: 5 per hour
        'otp_send' => env('RATE_LIMIT_OTP_SEND', '5,60'),

        // General API: 100 per hour
        'api_general' => env('RATE_LIMIT_API_GENERAL', '100,60'),

        // File uploads: 10 per minute
        'file_upload' => '10,1',

        // Password reset: 3 per hour
        'password_reset' => '3,60',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption & Data Protection
    |--------------------------------------------------------------------------
    */

    'encryption' => [
        'enabled' => true,
        'algorithm' => env('CIPHER', 'AES-256-CBC'),

        // Fields to automatically encrypt in database
        'encrypted_fields' => [
            'email' => false, // Email needed for queries
            'phone' => false, // Phone needed for communication
            'name' => false, // Name needed for display
            'admin_notes' => true, // Encrypt sensitive admin notes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password & Authentication Policies
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_numbers' => true,
        'require_special' => true,
        'max_age_days' => 90, // Force password change every 90 days
        'min_change_interval_days' => 1, // Minimum 1 day between changes
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP (One-Time Password) Configuration
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'enabled' => true,
        'length' => 6,
        'expiry_minutes' => 15,
        'max_attempts' => 5,
        'lockout_minutes' => 30,
        'rate_limit' => 5, // Max 5 OTP requests per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */

    'session' => [
        'secure' => env('SESSION_SECURE', false), // HTTPS only in production
        'http_only' => true,
        'same_site' => 'lax',
        'encrypt' => env('SESSION_ENCRYPT', false), // Encrypt session data in production
        'lifetime' => env('SESSION_LIFETIME', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */

    'csrf' => [
        'enabled' => true,
        'exclude_paths' => [
            // Exclude paths that don't need CSRF protection
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation & Sanitization
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'scan_files' => true, // Scan uploaded files for malware
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit & Logging
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'enabled' => true,
        'log_level' => env('LOG_LEVEL', 'warning'),
        'log_user_agent' => true,
        'log_ip_address' => true,
        'log_user_actions' => true,
        'retention_days' => 90,
    ],
];

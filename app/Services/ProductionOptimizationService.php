<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProductionOptimizationService
{
    /**
     * Run all production optimization commands.
     * Execute this during deployment to production.
     */
    public static function optimize(): void
    {
        Log::info('Starting production optimization...');

        try {
            // Clear and cache configuration
            Artisan::call('config:clear');
            Log::info('Configuration cache cleared');

            Artisan::call('config:cache');
            Log::info('Configuration cached');

            // Clear and cache routes
            Artisan::call('route:clear');
            Log::info('Route cache cleared');

            Artisan::call('route:cache');
            Log::info('Routes cached');

            // Cache views
            Artisan::call('view:clear');
            Log::info('View cache cleared');

            Artisan::call('view:cache');
            Log::info('Views cached');

            // Optimize autoloader
            Artisan::call('optimize');
            Log::info('Autoloader optimized');

            // Cache events
            Artisan::call('event:cache');
            Log::info('Events cached');

            Log::info('Production optimization completed successfully');
        } catch (\Exception $e) {
            Log::error('Production optimization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clear all caches (use before optimization or if needed during deployment).
     */
    public static function clearAllCaches(): void
    {
        Log::info('Clearing all caches...');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');

        Log::info('All caches cleared');
    }
}

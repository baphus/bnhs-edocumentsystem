<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Base job class for all queued jobs.
 * Implements common job patterns and production-ready configuration.
 */
abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of times a job should be attempted.
     */
    public int $tries = 3;

    /**
     * Maximum number of seconds a job should be allowed to run.
     */
    public int $timeout = 300;

    /**
     * Number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Get the backoff values for the job.
     * Implements exponential backoff: 30s, 60s, 120s
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        \Illuminate\Support\Facades\Log::error(
            'Job failed: ' . static::class,
            [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        // Optional: Send notification to admin
        // Notification::route('mail', config('mail.from.address'))
        //     ->notify(new JobFailedNotification($exception, static::class));
    }
}

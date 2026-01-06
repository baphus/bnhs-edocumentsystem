<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

class LogQueuedEmail
{
    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        // We no longer record a queued status in email logs to keep them concise.
        return;
    }
}

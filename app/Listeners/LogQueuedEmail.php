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
        try {
            $message = $event->message;
            
            // Get recipient email
            $recipients = $message->getTo();
            $recipientEmail = !empty($recipients) ? array_key_first($recipients) : null;
            
            // Get subject
            $subject = $message->getSubject() ?? 'No Subject';
            
            // Extract document_request_id from the email if available
            $documentRequestId = null;
            if (isset($event->data['documentRequest'])) {
                $documentRequestId = $event->data['documentRequest']->id ?? null;
            }
            
            // Create email log entry with 'queued' status
            EmailLog::create([
                'document_request_id' => $documentRequestId,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'status' => 'queued',
                'sent_at' => null,
                'delivered_at' => null,
                'error_message' => null,
            ]);
            
        } catch (\Exception $e) {
            // Log the error but don't disrupt the email sending process
            Log::error('Failed to log queued email: ' . $e->getMessage(), [
                'exception' => $e,
                'event' => class_basename($event),
            ]);
        }
    }
}

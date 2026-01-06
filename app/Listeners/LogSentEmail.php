<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentEmail
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;
            
            // Get recipient email (first TO address)
            $recipientEmail = null;
            $recipients = $message->getTo();
            if (is_array($recipients) && count($recipients) > 0) {
                $first = reset($recipients);
                // Symfony\Component\Mime\Address exposes getAddress()
                if (is_object($first) && method_exists($first, 'getAddress')) {
                    $recipientEmail = $first->getAddress();
                } elseif (is_array($first) && isset($first['address'])) {
                    $recipientEmail = $first['address'];
                }
            }
            
            // Get subject
            $subject = $message->getSubject() ?? 'No Subject';

            // Extract document_request_id from the email if available
            $documentRequestId = null;
            
            // Try to extract document request from the mailable
            $mailable = $event->sent;
            if ($mailable && property_exists($mailable, 'documentRequest') && $mailable->documentRequest) {
                $documentRequestId = $mailable->documentRequest->id;
            }

            // Promote any previously queued log for this email to a sent log
            $queuedLog = EmailLog::where('recipient_email', $recipientEmail)
                ->where('subject', $subject)
                ->where('status', 'queued')
                ->latest()
                ->first();

            if ($queuedLog) {
                $queuedLog->update([
                    'document_request_id' => $documentRequestId ?? $queuedLog->document_request_id,
                    'status' => 'sent',
                    'sent_at' => now(),
                    // We don't have a delivery webhook; mark delivered when SMTP send completes
                    'delivered_at' => now(),
                    'error_message' => null,
                ]);
                return;
            }
            
            // Check if this exact email was already logged very recently (prevent duplicates)
            $recentLog = EmailLog::where('recipient_email', $recipientEmail)
                ->where('subject', $subject)
                ->where('created_at', '>', now()->subSeconds(10))
                ->first();
            
            if ($recentLog) {
                return;
            }
            
            // Create email log entry
            EmailLog::create([
                'document_request_id' => $documentRequestId,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'status' => 'sent',
                'sent_at' => now(),
                // We don't have a delivery webhook; mark delivered when SMTP send completes
                'delivered_at' => now(),
                'error_message' => null,
            ]);
            
        } catch (\Exception $e) {
            // Log the error but don't disrupt the email sending process
            Log::error('Failed to log sent email: ' . $e->getMessage(), [
                'exception' => $e,
                'event' => class_basename($event),
            ]);
        }
    }
}

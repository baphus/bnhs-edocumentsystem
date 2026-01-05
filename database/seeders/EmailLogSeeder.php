<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\EmailLog;
use Illuminate\Database\Seeder;

class EmailLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requests = DocumentRequest::all();

        if ($requests->isEmpty()) {
            $this->command->warn('No document requests found. Please run DocumentRequestSeeder first.');
            return;
        }

        $createdCount = 0;

        foreach ($requests as $request) {
            // Log for initial request submission
            EmailLog::create([
                'document_request_id' => $request->id,
                'recipient_email' => $request->email,
                'subject' => 'Document Request Received - ' . $request->tracking_id,
                'status' => 'sent',
                'sent_at' => $request->created_at->copy()->addMinutes(1),
                'delivered_at' => $request->created_at->copy()->addMinutes(2),
            ]);
            $createdCount++;

            // Log for status updates if any
            if ($request->status !== 'Pending') {
                EmailLog::create([
                    'document_request_id' => $request->id,
                    'recipient_email' => $request->email,
                    'subject' => 'Document Request Status Update - ' . $request->status,
                    'status' => 'sent',
                    'sent_at' => $request->updated_at->copy()->subMinutes(5),
                    'delivered_at' => $request->updated_at->copy()->subMinutes(4),
                ]);
                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} email log entries.");
    }
}

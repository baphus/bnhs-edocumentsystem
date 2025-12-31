<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequestLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requests = DocumentRequest::all();
        $registrars = User::where('role', 'registrar')->get();

        if ($requests->isEmpty()) {
            $this->command->warn('No document requests found. Please run DocumentRequestSeeder first.');
            return;
        }

        if ($registrars->isEmpty()) {
            $this->command->warn('No registrar users found. Please run UserSeeder first.');
            return;
        }

        $createdCount = 0;

        foreach ($requests as $request) {
            $registrar = $registrars->random();
            $logs = $this->generateLogsForRequest($request, $registrar);
            
            foreach ($logs as $log) {
                RequestLog::create($log);
                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} request log entries.");
    }

    /**
     * Generate appropriate logs based on request status
     */
    private function generateLogsForRequest(DocumentRequest $request, User $registrar): array
    {
        $logs = [];
        $baseTime = $request->created_at;

        // All requests get a "submitted" log
        $logs[] = [
            'document_request_id' => $request->id,
            'user_id' => null, // Student submitted
            'action' => 'submitted',
            'old_value' => null,
            'new_value' => 'Pending',
            'description' => 'Document request submitted by student.',
            'created_at' => $baseTime,
            'updated_at' => $baseTime,
        ];

        // Add status change logs based on current status
        switch ($request->status) {
            case 'Verified':
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Pending',
                    'Verified',
                    'Student information has been verified.',
                    $baseTime->copy()->addHours(rand(1, 24))
                );
                break;

            case 'Processing':
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Pending',
                    'Verified',
                    'Student information has been verified.',
                    $baseTime->copy()->addHours(rand(1, 12))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Verified',
                    'Processing',
                    'Request is now being processed.',
                    $baseTime->copy()->addHours(rand(12, 24))
                );
                break;

            case 'Ready':
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Pending',
                    'Verified',
                    'Student information has been verified.',
                    $baseTime->copy()->addHours(rand(1, 12))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Verified',
                    'Processing',
                    'Request is now being processed.',
                    $baseTime->copy()->addHours(rand(12, 24))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Processing',
                    'Ready',
                    'Document is ready for pickup.',
                    $baseTime->copy()->addDays(rand(1, 5))
                );
                break;

            case 'Completed':
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Pending',
                    'Verified',
                    'Student information has been verified.',
                    $baseTime->copy()->addHours(rand(1, 12))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Verified',
                    'Processing',
                    'Request is now being processed.',
                    $baseTime->copy()->addHours(rand(12, 24))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Processing',
                    'Ready',
                    'Document is ready for pickup.',
                    $baseTime->copy()->addDays(rand(1, 5))
                );
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Ready',
                    'Completed',
                    'Document released to student.',
                    $baseTime->copy()->addDays(rand(5, 10))
                );
                break;

            case 'Rejected':
                $logs[] = $this->createStatusChangeLog(
                    $request,
                    $registrar,
                    'Pending',
                    'Rejected',
                    'Request rejected. ' . ($request->admin_notes ?? 'See notes for details.'),
                    $baseTime->copy()->addHours(rand(2, 48))
                );
                break;
        }

        // Randomly add view logs
        if (rand(0, 1) && $request->status !== 'pending') {
            $logs[] = [
                'document_request_id' => $request->id,
                'user_id' => $registrar->id,
                'action' => 'viewed',
                'old_value' => null,
                'new_value' => null,
                'description' => 'Request viewed by registrar.',
                'created_at' => $baseTime->copy()->addHours(rand(1, 12)),
                'updated_at' => $baseTime->copy()->addHours(rand(1, 12)),
            ];
        }

        // Randomly add note update logs
        if (rand(0, 2) === 0 && $request->admin_notes) {
            $logs[] = [
                'document_request_id' => $request->id,
                'user_id' => $registrar->id,
                'action' => 'note_added',
                'old_value' => null,
                'new_value' => $request->admin_notes,
                'description' => 'Admin note added to request.',
                'created_at' => $baseTime->copy()->addHours(rand(2, 24)),
                'updated_at' => $baseTime->copy()->addHours(rand(2, 24)),
            ];
        }

        return $logs;
    }

    /**
     * Create a status change log entry
     */
    private function createStatusChangeLog(
        DocumentRequest $request,
        User $registrar,
        string $oldStatus,
        string $newStatus,
        string $description,
        $timestamp
    ): array {
        return [
            'document_request_id' => $request->id,
            'user_id' => $registrar->id,
            'action' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'description' => $description,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DocumentRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = DocumentType::all();
        $registrar = User::where('role', 'registrar')->first();

        if ($documentTypes->isEmpty()) {
            $this->command->warn('No document types found. Please run DocumentTypeSeeder first.');
            return;
        }

        // Sample Filipino student names
        $students = [
            ['first_name' => 'Juan', 'middle_name' => 'Santos', 'last_name' => 'Dela Cruz'],
            ['first_name' => 'Maria', 'middle_name' => 'Garcia', 'last_name' => 'Reyes'],
            ['first_name' => 'Jose', 'middle_name' => 'Lopez', 'last_name' => 'Bautista'],
            ['first_name' => 'Ana', 'middle_name' => 'Mendoza', 'last_name' => 'Santos'],
            ['first_name' => 'Miguel', 'middle_name' => 'Cruz', 'last_name' => 'Gonzales'],
            ['first_name' => 'Sofia', 'middle_name' => 'Ramos', 'last_name' => 'Aquino'],
            ['first_name' => 'Carlos', 'middle_name' => 'Villanueva', 'last_name' => 'Torres'],
            ['first_name' => 'Beatriz', 'middle_name' => 'Fernandez', 'last_name' => 'Lim'],
            ['first_name' => 'Rafael', 'middle_name' => 'Pascual', 'last_name' => 'Domingo'],
            ['first_name' => 'Isabella', 'middle_name' => 'Rivera', 'last_name' => 'Castro'],
            ['first_name' => 'Gabriel', 'middle_name' => 'Soriano', 'last_name' => 'Flores'],
            ['first_name' => 'Patricia', 'middle_name' => 'Navarro', 'last_name' => 'Mercado'],
            ['first_name' => 'Antonio', 'middle_name' => 'Velasco', 'last_name' => 'Tan'],
            ['first_name' => 'Camille', 'middle_name' => 'Aguilar', 'last_name' => 'Sy'],
            ['first_name' => 'Diego', 'middle_name' => 'Padilla', 'last_name' => 'Ong'],
        ];

        $gradeLevels = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
        $sections = ['Einstein', 'Newton', 'Galileo', 'Curie', 'Darwin', 'Mendel', 'Faraday'];
        $trackStrands = [
            'STEM - Science, Technology, Engineering, and Mathematics',
            'ABM - Accountancy, Business, and Management',
            'HUMSS - Humanities and Social Sciences',
            'GAS - General Academic Strand',
            'TVL - Technical-Vocational-Livelihood',
            'Arts and Design',
            'Sports',
        ];

        $purposes = [
            'For college enrollment',
            'For scholarship application',
            'For transfer to another school',
            'For employment requirement',
            'For personal records',
            'For government requirements',
            'For visa application',
            'For organization membership',
        ];

        // Status values must match the database enum exactly (capitalized)
        $statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

        // Generate 50 sample document requests
        for ($i = 0; $i < 50; $i++) {
            $student = $students[array_rand($students)];
            $docType = $documentTypes->random();
            $status = $statuses[array_rand($statuses)];
            $gradeLevel = $gradeLevels[array_rand($gradeLevels)];
            
            // Only add track/strand for Grade 11 and 12
            $trackStrand = in_array($gradeLevel, ['Grade 11', 'Grade 12']) 
                ? $trackStrands[array_rand($trackStrands)] 
                : null;

            // Calculate dates based on status
            $createdAt = Carbon::now()->subDays(rand(0, 60));
            $estimatedCompletion = null;
            $completedAt = null;
            $processedBy = null;

            if (in_array($status, ['Processing', 'Ready', 'Completed'])) {
                $estimatedCompletion = $createdAt->copy()->addDays($docType->processing_days ?? 5);
                $processedBy = $registrar?->id;
            }

            if (in_array($status, ['Ready', 'Completed'])) {
                $completedAt = $createdAt->copy()->addDays(rand(1, $docType->processing_days ?? 5));
            }

            // Generate school year
            $schoolYear = rand(2020, 2025) . '-' . (rand(2020, 2025) + 1);

            DocumentRequest::create([
                'email' => strtolower($student['first_name']) . '.' . strtolower($student['last_name']) . rand(100, 999) . '@gmail.com',
                'first_name' => $student['first_name'],
                'middle_name' => $student['middle_name'],
                'last_name' => $student['last_name'],
                'lrn' => $this->generateLRN(),
                'grade_level' => $gradeLevel,
                'section' => $sections[array_rand($sections)],
                'track_strand' => $trackStrand,
                'school_year_last_attended' => $schoolYear,
                'document_type_id' => $docType->id,
                'purpose' => $purposes[array_rand($purposes)],
                'quantity' => rand(1, 3),
                'status' => $status,
                'estimated_completion_date' => $estimatedCompletion,
                'completed_at' => $completedAt,
                'admin_notes' => $this->generateAdminNotes($status),
                'processed_by' => $processedBy,
                'otp_verified' => rand(0, 1) ? true : false,
                'signature' => rand(0, 1) ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 48)),
            ]);
        }

        $this->command->info('Created 50 sample document requests.');
    }

    /**
     * Generate a random 12-digit LRN (Learner Reference Number)
     */
    private function generateLRN(): string
    {
        // LRN format: first digit is region, followed by 11 random digits
        return rand(1, 9) . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    /**
     * Generate admin notes based on status
     */
    private function generateAdminNotes(string $status): ?string
    {
        $notes = [
            'Pending' => [
                null,
                'Waiting for document verification.',
                'Student information under review.',
            ],
            'Verified' => [
                'Student information verified.',
                'Documents verified. Ready for processing.',
            ],
            'Processing' => [
                'Currently being processed.',
                'Document preparation in progress.',
                'Awaiting principal signature.',
                'Being reviewed by registrar.',
            ],
            'Ready' => [
                'Document is ready for pickup.',
                'Please claim within 30 days.',
                'Ready. Notify student via email.',
            ],
            'Completed' => [
                'Released to student.',
                'Claimed by authorized representative.',
                'Released and signed for.',
            ],
            'Rejected' => [
                'Incomplete requirements. Please resubmit.',
                'Student records not found. Please verify information.',
                'Duplicate request. Previous request already processed.',
                'Invalid LRN. Please check and resubmit.',
            ],
        ];

        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
}

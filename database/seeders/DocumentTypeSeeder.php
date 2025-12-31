<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            // Informal Documents (1-2 processing days)
            [
                'name' => 'Grade Slip (Quarter 1)',
                'category' => 'Informal',
                'description' => 'Summary of grades for the first quarter',
                'processing_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Grade Slip (Quarter 2)',
                'category' => 'Informal',
                'description' => 'Summary of grades for the second quarter',
                'processing_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Grade Slip (Quarter 3)',
                'category' => 'Informal',
                'description' => 'Summary of grades for the third quarter',
                'processing_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Grade Slip (Quarter 4)',
                'category' => 'Informal',
                'description' => 'Summary of grades for the fourth quarter',
                'processing_days' => 1,
                'is_active' => true,
            ],

            // Official Documents (3-5 processing days)
            [
                'name' => 'Good Moral Certificate',
                'category' => 'Official',
                'description' => 'Certificate of good moral character',
                'processing_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Enrollment Certificate',
                'category' => 'Official',
                'description' => 'Certificate confirming enrollment status',
                'processing_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Certificate of Honors',
                'category' => 'Official',
                'description' => 'Certificate for academic honors and awards',
                'processing_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Report Card (Form 138)',
                'category' => 'Official',
                'description' => 'Official student report card',
                'processing_days' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Permanent Record (Form 137)',
                'category' => 'Official',
                'description' => 'Official permanent academic record',
                'processing_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Diploma',
                'category' => 'Official',
                'description' => 'Official graduation diploma',
                'processing_days' => 7,
                'is_active' => true,
            ],

            // Certified Documents (7-14 processing days)
            [
                'name' => 'Certified True Copy of Report Card',
                'category' => 'Certified',
                'description' => 'Certified true copy of Form 138',
                'processing_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Certified True Copy of Diploma',
                'category' => 'Certified',
                'description' => 'Certified true copy of diploma',
                'processing_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Reconstructed Diploma',
                'category' => 'Certified',
                'description' => 'Reconstructed diploma for lost or damaged original',
                'processing_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Reconstructed Report Card',
                'category' => 'Certified',
                'description' => 'Reconstructed report card for lost or damaged original',
                'processing_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'CAV (Certification, Authentication, Verification)',
                'category' => 'Certified',
                'description' => 'Official certification for document authentication',
                'processing_days' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($documentTypes as $docType) {
            DocumentType::updateOrCreate(
                ['name' => $docType['name']],
                $docType
            );
        }
    }
}

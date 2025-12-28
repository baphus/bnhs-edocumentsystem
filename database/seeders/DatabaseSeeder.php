<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@bnhs.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Create Registrar
        User::create([
            'name' => 'Registrar Admin',
            'email' => 'registrar@bnhs.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'registrar',
        ]);

        // Seed Document Types
        $documentTypes = [
            // Informal Documents
            ['name' => 'Grade Slip (Quarter 1)', 'category' => 'Informal'],
            ['name' => 'Grade Slip (Quarter 2)', 'category' => 'Informal'],
            ['name' => 'Grade Slip (Quarter 3)', 'category' => 'Informal'],
            ['name' => 'Grade Slip (Quarter 4)', 'category' => 'Informal'],

            // Official Documents
            ['name' => 'Good Moral Certificate', 'category' => 'Official'],
            ['name' => 'Enrollment Certificate', 'category' => 'Official'],
            ['name' => 'Certificate of Honors', 'category' => 'Official'],
            ['name' => 'Report Card (Form 138)', 'category' => 'Official'],
            ['name' => 'Permanent Record (Form 137)', 'category' => 'Official'],
            ['name' => 'Diploma', 'category' => 'Official'],

            // Certified Documents
            ['name' => 'Certified True Copy of Report Card', 'category' => 'Certified'],
            ['name' => 'Certified True Copy of Diploma', 'category' => 'Certified'],
            ['name' => 'Reconstructed Diploma', 'category' => 'Certified'],
            ['name' => 'Reconstructed Report Card', 'category' => 'Certified'],
            ['name' => 'CAV (Certification, Authentication, Verification)', 'category' => 'Certified'],
        ];

        foreach ($documentTypes as $docType) {
            DocumentType::create($docType);
        }

        // Seed default settings
        $this->call(SettingsSeeder::class);
    }
}

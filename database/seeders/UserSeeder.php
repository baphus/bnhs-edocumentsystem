<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::updateOrCreate(
            ['email' => 'admin@bnhs.edu.ph'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Create Primary Registrar
        User::updateOrCreate(
            ['email' => 'registrar@bnhs.edu.ph'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password'),
                'role' => User::ROLE_REGISTRAR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(2),
            ]
        );

        // Create Additional Registrar Staff
        User::updateOrCreate(
            ['email' => 'registrar2@bnhs.edu.ph'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password'),
                'role' => User::ROLE_REGISTRAR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(1),
            ]
        );

        User::updateOrCreate(
            ['email' => 'registrar3@bnhs.edu.ph'],
            [
                'name' => 'Ana Reyes',
                'password' => Hash::make('password'),
                'role' => User::ROLE_REGISTRAR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Create an inactive registrar for testing
        User::updateOrCreate(
            ['email' => 'suspended.staff@bnhs.edu.ph'],
            [
                'name' => 'Pedro Suspended',
                'password' => Hash::make('password'),
                'role' => User::ROLE_REGISTRAR,
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }
}

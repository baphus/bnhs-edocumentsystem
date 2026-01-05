<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@bnhs.edu.ph'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Create registrar user
        User::firstOrCreate(
            ['email' => 'registrar@bnhs.edu.ph'],
            [
                'name' => 'Registrar Office',
                'password' => Hash::make('password'),
                'role' => User::ROLE_REGISTRAR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Create sample guest/requester user
        User::firstOrCreate(
            ['email' => 'guest@bnhs.edu.ph'],
            [
                'name' => 'Sample Guest',
                'password' => Hash::make('password'),
                'role' => User::ROLE_GUEST,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@bnhs.edu.ph / password');
        $this->command->info('Registrar: registrar@bnhs.edu.ph / password');
        $this->command->info('Guest: guest@bnhs.edu.ph / password');
    }
}

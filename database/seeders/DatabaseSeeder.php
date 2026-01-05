<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core data seeders
            UserSeeder::class,
            DocumentTypeSeeder::class,
            SettingsSeeder::class,
            
            // Sample data seeders
            DocumentRequestSeeder::class,
            RequestLogSeeder::class,
            EmailLogSeeder::class,
        ]);

        $this->command->info('All seeders completed successfully!');
    }
}

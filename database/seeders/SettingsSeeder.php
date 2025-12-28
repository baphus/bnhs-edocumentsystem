<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::set('reminder_interval_days', 3, 'integer', 'Days between reminder emails');
        Setting::set('max_reminder_count', 5, 'integer', 'Maximum number of reminder emails');
        Setting::set('otp_expiry_minutes', 10, 'integer', 'OTP expiration time in minutes');
        Setting::set('school_year_current', now()->format('Y') . '-' . (now()->year + 1), 'string', 'Current school year');
        Setting::set('maintenance_mode', false, 'boolean', 'Enable/disable maintenance mode');
    }
}

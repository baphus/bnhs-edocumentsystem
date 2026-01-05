<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(): Response
    {
        $settings = [
            'reminder_interval_days' => Setting::get('reminder_interval_days', 3),
            'max_reminder_count' => Setting::get('max_reminder_count', 5),
            'otp_expiry_minutes' => Setting::get('otp_expiry_minutes', 10),
            'school_year_current' => Setting::get('school_year_current', now()->format('Y') . '-' . (now()->year + 1)),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
        ];

        return Inertia::render('Admin/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'reminder_interval_days' => 'required|integer|min:1|max:30',
            'max_reminder_count' => 'required|integer|min:1|max:10',
            'otp_expiry_minutes' => 'required|integer|min:5|max:60',
            'school_year_current' => 'required|string|max:20',
            'maintenance_mode' => 'boolean',
        ]);

        Setting::set('reminder_interval_days', $validated['reminder_interval_days'], 'integer', 'Days between reminder emails');
        Setting::set('max_reminder_count', $validated['max_reminder_count'], 'integer', 'Maximum number of reminder emails to send');
        Setting::set('otp_expiry_minutes', $validated['otp_expiry_minutes'], 'integer', 'OTP expiration time in minutes');
        Setting::set('school_year_current', $validated['school_year_current'], 'string', 'Current school year');
        Setting::set('maintenance_mode', $validated['maintenance_mode'] ?? false, 'boolean', 'Enable/disable maintenance mode');

        return back()->with('success', 'Settings updated successfully.');
    }
}

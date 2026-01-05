<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixRegistrarRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:registrar-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the registrar user role and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing registrar role...');

        // 1. Ensure registrar user exists
        $registrar = User::where('email', 'registrar@bnhs.edu.ph')->first();

        if (!$registrar) {
            $this->info('Registrar user not found. Creating...');
            $registrar = User::create([
                'name' => 'Maria Santos',
                'email' => 'registrar@bnhs.edu.ph',
                'password' => Hash::make('password'),
                'role' => 'registrar',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        } else {
            $this->info('Registrar user found. Updating role...');
            $registrar->role = 'registrar';
            $registrar->status = 'active';
            $registrar->save();
        }

        $this->info("Registrar user ({$registrar->email}) is set to role: {$registrar->role}");

        // 2. Check Admin user too
        $admin = User::where('email', 'admin@bnhs.edu.ph')->first();
        if ($admin) {
            $admin->role = 'admin';
            $admin->save();
            $this->info("Admin user ({$admin->email}) is set to role: {$admin->role}");
        }

        $this->info('Done.');
    }
}

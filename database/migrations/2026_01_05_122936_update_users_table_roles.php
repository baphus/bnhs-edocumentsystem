<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Update the role enum to include new roles
            $table->enum('role', ['admin', 'registrar', 'guest'])->default('guest')->change();
            
            // Status column already exists from previous migration (with 'active', 'suspended')
            // Modify it to include both old and new values for compatibility
            if (Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'inactive'])->default('active')->change();
            }
            
            // Add last_login_at column if it doesn't exist
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            
            // Add soft deletes if it doesn't exist
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Update existing roles to match new structure
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['principal', 'student'])->update(['role' => 'guest']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to old roles
            $table->enum('role', ['registrar', 'principal', 'student'])->default('student')->change();
            
            // Note: We don't drop status, last_login_at, or deleted_at columns
            // as they might be needed for other functionality
        });
        
        // Revert role changes
        DB::table('users')->where('role', 'admin')->update(['role' => 'registrar']);
        DB::table('users')->where('role', 'guest')->update(['role' => 'student']);
    }
};

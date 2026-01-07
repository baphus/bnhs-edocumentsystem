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
        // PostgreSQL: Drop old constraints first
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
        
        Schema::table('users', function (Blueprint $table) {
            // Change role to varchar and add constraint separately
            $table->string('role', 255)->default('guest')->change();
            
            // Add last_login_at column if it doesn't exist
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            
            // Add soft deletes if it doesn't exist
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Update existing roles to match new structure
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['principal', 'student'])->update(['role' => 'guest']);
        
        // Add check constraints after migration
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'registrar', 'guest'))");
        
        // Update status column if it exists
        if (Schema::hasColumn('users', 'status')) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
            DB::statement("ALTER TABLE users ALTER COLUMN status TYPE varchar(255)");
            DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'active'");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'suspended', 'inactive'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraints
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
        
        Schema::table('users', function (Blueprint $table) {
            // Revert to old roles
            $table->string('role', 255)->default('student')->change();
            
            // Note: We don't drop status, last_login_at, or deleted_at columns
            // as they might be needed for other functionality
        });
        
        // Revert role changes
        DB::table('users')->where('role', 'admin')->update(['role' => 'registrar']);
        DB::table('users')->where('role', 'guest')->update(['role' => 'student']);
    }
};

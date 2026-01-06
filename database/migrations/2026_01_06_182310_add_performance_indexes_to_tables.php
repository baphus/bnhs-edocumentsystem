<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if SQLite for development (some indexes already exist)
        $driver = Schema::getConnection()->getDriverName();
        
        // Add missing indexes to audit_logs for user search optimization
        Schema::table('audit_logs', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                // Only add new indexes not in previous migrations
                $table->index(['user_role', 'created_at'], 'idx_role_date');
                $table->index(['model_type', 'created_at'], 'idx_model_date');
                $table->index('description'); // For LIKE searches
            }
        });

        // Add indexes to document_requests for better performance  
        Schema::table('document_requests', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                // Composite indexes for common queries not already added
                $table->index(['processed_by', 'status'], 'idx_processor_status');
            }
        });

        // Add indexes to request_logs
        Schema::table('request_logs', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                $table->index(['user_id', 'created_at'], 'idx_user_date_logs');
                $table->index('action');
            }
        });

        // Add indexes to users for faster lookups
        Schema::table('users', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                $table->index('role', 'idx_users_role');
                $table->index('status');
                $table->index(['role', 'status'], 'idx_role_status');
            }
        });

        // Add indexes to email_logs (status index already exists in create migration)
        Schema::table('email_logs', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                // Composite index not covered by previous migration
                $table->index('email', 'idx_email_email');
            }
        });

        // Add indexes to document_types for better counting
        Schema::table('document_types', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                $table->index('category');
                $table->index('is_active');
                $table->index(['category', 'is_active'], 'idx_category_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            return; // Skip on SQLite
        }
        
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_role_date');
            $table->dropIndex('idx_model_date');
            $table->dropIndex(['description']);
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex('idx_processor_status');
        });

        Schema::table('request_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_date_logs');
            $table->dropIndex(['action']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex(['status']);
            $table->dropIndex('idx_role_status');
        });

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropIndex('idx_email_email');
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_active']);
            $table->dropIndex('idx_category_active');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes for production optimization and performance tuning.
     * Run this migration in production to ensure optimal query performance.
     */
    public function up(): void
    {
        // Skip on SQLite (dev/test) to avoid duplicate index issues; intended for production
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Index for document_requests table - frequently queried columns
        Schema::table('document_requests', function (Blueprint $table) {
            // Composite index for common query patterns
            $table->index(['email', 'status', 'created_at']);
            $table->index(['tracking_id', 'deleted_at']);
            $table->index(['document_type_id', 'status']);
            $table->index(['otp_verified', 'otp_expires_at']);
        });

        // Index for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at']);
            $table->index(['email_verified_at']);
        });

        // Index for otps table
        Schema::table('otps', function (Blueprint $table) {
            $table->index(['email', 'expires_at']);
            $table->index(['code', 'created_at']);
        });

        // Index for request_logs table
        Schema::table('request_logs', function (Blueprint $table) {
            $table->index(['document_request_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        // Index for email_logs table
        Schema::table('email_logs', function (Blueprint $table) {
            $table->index(['recipient_email', 'sent_at']);
              $table->index(['status', 'created_at']);
        });

        // Note: sessions table indexes (user_id, last_activity) already exist
        // from Laravel's default sessions migration (0001_01_01_000000_create_users_table.php)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip on SQLite (dev/test) to avoid index issues; intended for production
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex(['email', 'status', 'created_at']);
            $table->dropIndex(['tracking_id', 'deleted_at']);
            $table->dropIndex(['document_type_id', 'status']);
            $table->dropIndex(['otp_verified', 'otp_expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'created_at']);
            $table->dropIndex(['email_verified_at']);
        });

        Schema::table('otps', function (Blueprint $table) {
            $table->dropIndex(['email', 'expires_at']);
            $table->dropIndex(['code', 'created_at']);
        });

        Schema::table('request_logs', function (Blueprint $table) {
            $table->dropIndex(['document_request_id', 'created_at']);
            $table->dropIndex(['action', 'created_at']);
        });

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropIndex(['recipient_email', 'sent_at']);
              $table->dropIndex(['status', 'created_at']);
        });

        // Note: sessions table indexes are not dropped as they're managed
        // by Laravel's default sessions migration
    }
};

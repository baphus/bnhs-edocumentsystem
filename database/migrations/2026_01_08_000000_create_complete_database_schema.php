<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Complete BNHS E-Document System Schema
     * Optimized for PostgreSQL (Supabase)
     */
    public function up(): void
    {
        // ============================================
        // 1. CORE SYSTEM TABLES
        // ============================================
        
        // Users table - Admin staff only (registrar, admin)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 255)->default('guest');
            $table->string('status', 255)->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('email');
            $table->index('role');
            $table->index('status');
        });
        
        // Add constraints for users
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'registrar', 'guest'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'suspended', 'inactive'))");

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });

        // ============================================
        // 2. DOCUMENT MANAGEMENT TABLES
        // ============================================
        
        // Document types - Different documents students can request
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 255);
            $table->decimal('price', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->integer('processing_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });
        
        DB::statement("ALTER TABLE document_types ADD CONSTRAINT document_types_category_check CHECK (category IN ('Official', 'Informal', 'Certified'))");

        // Academic tracks - STEM, ABM, HUMSS, etc.
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        // Document requests - Main table with embedded student info
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            
            // Tracking
            $table->string('tracking_id')->unique();
            
            // Student Information (no user accounts - passwordless system)
            $table->string('email')->index();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('lrn', 12)->index();
            $table->string('grade_level');
            $table->string('section');
            $table->string('track_strand')->nullable();
            $table->string('school_year_last_attended');
            $table->string('photo_path')->nullable();
            
            // Document Request Details
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->text('purpose');
            $table->integer('quantity')->default(1);
            $table->text('signature')->nullable();
            
            // Status and Processing
            $table->string('status', 255)->default('Pending')->index();
            $table->text('admin_remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('estimated_completion_date')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // OTP Verification
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('otp_verified')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            $table->index('tracking_id');
            $table->index('created_at');
        });
        
        DB::statement("ALTER TABLE document_requests ADD CONSTRAINT document_requests_status_check CHECK (status IN ('Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'))");

        // ============================================
        // 3. LOGGING AND TRACKING TABLES
        // ============================================
        
        // Request logs - Track all changes to document requests
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->nullable()->constrained('document_requests')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['document_request_id', 'created_at']);
        });

        // Audit logs - Security and admin activity tracking
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_role')->nullable();
            $table->string('action')->index();
            $table->string('model_type')->nullable()->index();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('created_at');
        });

        // Email logs - Track email delivery
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->nullable()->constrained('document_requests')->onDelete('set null');
            $table->string('recipient_email')->index();
            $table->string('subject');
            $table->string('status', 255)->default('queued')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
        
        DB::statement("ALTER TABLE email_logs ADD CONSTRAINT email_logs_status_check CHECK (status IN ('queued', 'sent', 'delivered', 'failed'))");

        // OTPs table - Email verification codes
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code', 6);
            $table->string('purpose')->default('request');
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
            
            $table->index(['email', 'code']);
            $table->index('expires_at');
        });

        // ============================================
        // 4. SYSTEM CONFIGURATION TABLES
        // ============================================
        
        // Settings table - Application configuration
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ============================================
        // 5. LARAVEL SYSTEM TABLES
        // ============================================
        
        // Cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs table - Background job queue
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->text('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids');
            $table->text('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->string('queue');
            $table->text('payload');
            $table->text('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to handle foreign key constraints
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('otps');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('request_logs');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

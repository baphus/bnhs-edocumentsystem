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
        // 1. Drop old tables that are no longer needed
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('students');

        // 2. Modify users table - remove 'student' role
        Schema::table('users', function (Blueprint $table) {
            // Change role enum to only allow admin roles
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'registrar'])->default('registrar')->after('password');
        });

        // 3. Create new document_requests table with all student info embedded
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            
            // Unique tracking ID (e.g., BNHS-2025-XXXX or UUID)
            $table->string('tracking_id')->unique();
            
            // Student Information (no foreign key - students don't have accounts)
            $table->string('email');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('lrn', 12);
            $table->string('grade_level'); // Grade 7-12
            $table->string('section');
            $table->string('track_strand')->nullable(); // Only for Grade 11-12
            $table->string('school_year_last_attended'); // e.g., "2023-2024"
            $table->string('photo_path')->nullable(); // 2x2 ID photo
            
            // Document Request Details
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->text('purpose');
            
            // Request Status
            $table->enum('status', [
                'Pending',
                'Verified',
                'Processing',
                'Ready',
                'Completed',
                'Rejected'
            ])->default('Pending');
            
            // Admin Notes (for registrar to add comments)
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // OTP Verification
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('otp_verified')->default(false);
            
            $table->timestamps();
            
            // Indexes for faster lookups
            $table->index('email');
            $table->index('tracking_id');
            $table->index('lrn');
            $table->index('status');
        });

        // 4. Create request_logs table for tracking status changes
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Admin who made the change
            $table->string('action'); // e.g., 'status_change', 'note_added'
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Create otps table for email verification before form submission
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('code', 6);
            $table->string('purpose')->default('request'); // 'request' or 'tracking'
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['email', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('otps');

        // Restore old role enum
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['registrar', 'principal', 'student'])->default('student')->after('password');
        });

        // Recreate old tables (simplified - would need full recreation in production)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('lrn', 12)->unique();
            $table->string('grade_level')->nullable();
            $table->string('section')->nullable();
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['Pending', 'For Verification', 'Approved', 'Ready for Pickup', 'Released'])->default('Pending');
            $table->text('purpose');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};


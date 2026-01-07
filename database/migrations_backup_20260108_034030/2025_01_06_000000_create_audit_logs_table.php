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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // User who performed the action (nullable for system actions)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_role')->nullable(); // Snapshot of role at the time
            
            // Action details
            $table->string('action')->index(); // CREATE, UPDATE, DELETE, LOGIN, ETC
            $table->string('model_type')->nullable()->index(); // e.g. App\Models\DocumentRequest
            $table->unsignedBigInteger('model_id')->nullable();
            
            // Content
            $table->text('description'); // Human readable
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Request details
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();

            // Index for faster searching/filtering
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

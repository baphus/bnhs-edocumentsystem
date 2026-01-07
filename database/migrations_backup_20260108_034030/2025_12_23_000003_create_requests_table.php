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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->string('status', 255)->default('Pending');
            $table->text('purpose');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('request_id');
            $table->index('status');
            $table->index('created_at');
        });
        
        DB::statement("ALTER TABLE requests ADD CONSTRAINT requests_status_check CHECK (status IN ('Pending', 'For Verification', 'Approved', 'Ready for Pickup', 'Released'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};


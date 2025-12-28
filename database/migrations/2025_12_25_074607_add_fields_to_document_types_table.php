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
        Schema::table('document_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->integer('processing_days')->default(3)->after('description');
            $table->boolean('is_active')->default(true)->after('processing_days');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['description', 'processing_days', 'is_active', 'deleted_at']);
        });
    }
};

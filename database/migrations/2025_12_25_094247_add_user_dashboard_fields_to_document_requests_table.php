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
        Schema::table('document_requests', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('purpose');
            $table->date('estimated_completion_date')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('estimated_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'estimated_completion_date', 'completed_at']);
        });
    }
};

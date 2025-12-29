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
        Schema::table('request_logs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['document_request_id']);
        });

        Schema::table('request_logs', function (Blueprint $table) {
            // Modify the column to be nullable
            $table->unsignedBigInteger('document_request_id')->nullable()->change();
        });

        Schema::table('request_logs', function (Blueprint $table) {
            // Recreate the foreign key constraint
            $table->foreign('document_request_id')
                ->references('id')
                ->on('document_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_logs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['document_request_id']);
        });

        Schema::table('request_logs', function (Blueprint $table) {
            // Modify the column to be not nullable
            $table->unsignedBigInteger('document_request_id')->nullable(false)->change();
        });

        Schema::table('request_logs', function (Blueprint $table) {
            // Recreate the foreign key constraint
            $table->foreign('document_request_id')
                ->references('id')
                ->on('document_requests')
                ->onDelete('cascade');
        });
    }
};

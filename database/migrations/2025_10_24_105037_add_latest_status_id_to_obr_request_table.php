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
        Schema::table('obr_request', function (Blueprint $table) {
            // Add the new column.
            // It must be nullable() so existing records don't cause errors.
            // We'll constrain it to the paper_trail_statuses table.
            $table->foreignId('latest_status_id')
                  ->nullable()
                  ->constrained('paper_trail_statuses')
                  ->onDelete('set null'); // If a status is deleted, set this to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obr_request', function (Blueprint $table) {
            // This is the proper way to drop a foreign key column
            $table->dropForeign(['latest_status_id']);
            $table->dropColumn('latest_status_id');
        });
    }
};

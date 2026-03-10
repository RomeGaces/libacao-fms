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
        Schema::table('paper_trail_statuses', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['request_id']); // Laravel will find the FK by column
            
            // Then drop the unique constraint
            $table->dropUnique('paper_trail_statuses_request_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_trail_statuses', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique('request_id');
            
            // Re-add the foreign key
            $table->foreign('request_id')
                  ->references('id')
                  ->on('other_table'); // Replace 'other_table' with the actual referenced table
        });
    }
};

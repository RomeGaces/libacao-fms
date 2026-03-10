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
            // Drop the column
            $table->dropColumn('approval_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_trail_statuses', function (Blueprint $table) {
            // Add the column back if we roll back
            // We place it back where it was for consistency
            $table->string('approval_title')->after('internal_step_id');
        });
    }
};
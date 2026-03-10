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
        Schema::table('steps', function (Blueprint $table) {
            // Drop the old string column if it exists
            if (Schema::hasColumn('steps', 'office_code_step_owner')) {
                $table->dropColumn('office_code_step_owner');
            }

            // Add the new foreignId column (if it doesn't already exist)
            if (!Schema::hasColumn('steps', 'office_code_id')) {
                 $table->foreignId('office_code_id')
                       ->nullable() // <-- Allow null values
                       ->after('set_id') // Place it after the set_id column
                       ->constrained('office_codes')
                       ->onDelete('set null'); // <-- Updated as requested
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            // Drop the new column
            if (Schema::hasColumn('steps', 'office_code_id')) {
                $table->dropForeign(['office_code_id']);
                $table->dropColumn('office_code_id');
            }

            // Re-add the old string column (if it doesn't exist)
             if (!Schema::hasColumn('steps', 'office_code_step_owner')) {
                 $table->string('office_code_step_owner');
             }
        });
    }
};


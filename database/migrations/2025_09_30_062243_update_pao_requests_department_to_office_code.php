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
        Schema::table('pao_requests', function (Blueprint $table) {
            // Drop old foreign key first (adjust if FK name is different)
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');

            // Add the new column referencing office_codes
            $table->foreignId('office_code_id')
                  ->after('id')
                  ->constrained('office_codes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pao_requests', function (Blueprint $table) {
            // Rollback: remove office_code_id
            $table->dropForeign(['office_code_id']);
            $table->dropColumn('office_code_id');

            // Re-add department_id back
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
        });
    }
};

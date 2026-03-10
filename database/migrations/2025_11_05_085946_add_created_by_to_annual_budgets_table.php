<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('annual_budgets', function (Blueprint $table) {
            // Add nullable FK to users; nullOnDelete so historical rows remain if user is deleted
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('annual_budget'); // place after amount (adjust as you like)
        });
    }

    public function down(): void
    {
        Schema::table('annual_budgets', function (Blueprint $table) {
            // Drop FK then column (order matters)
            $table->dropConstrainedForeignId('created_by');
        });
    }
};

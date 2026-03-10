<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_budgets', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique(); // Ensures only one budget record per year
            $table->decimal('annual_budget', 15, 2); // Suitable for monetary values
            $table->timestamps();
            $table->softDeletes(); // For soft-deleting records
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_budgets');
    }
};
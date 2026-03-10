<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_code_budgets', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to the office_codes table
            $table->foreignId('office_code_id')->constrained()->onDelete('cascade');

            // Foreign key to the annual_budgets table
            $table->foreignId('annual_budget_id')->constrained()->onDelete('cascade');
            
            $table->decimal('budget', 15, 2); // The allocated portion
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_code_budgets');
    }
};
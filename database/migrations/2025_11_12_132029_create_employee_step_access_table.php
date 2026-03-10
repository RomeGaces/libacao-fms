<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_step_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('internal_step_id')->constrained('internal_steps')->onDelete('cascade');
            $table->boolean('has_access')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['employee_id', 'internal_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_step_access');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->string('position_title')->nullable();
            $table->string('department_agency')->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('status_of_appointment')->nullable();
            $table->boolean('is_gov_service')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_experiences');
    }
};

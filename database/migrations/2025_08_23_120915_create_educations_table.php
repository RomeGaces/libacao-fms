<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('level')->nullable(); // elementary, secondary, vocational, college, graduate
            $table->string('school_name')->nullable();
            $table->string('degree_course')->nullable();
            $table->string('year_graduated')->nullable();
            $table->integer('units_earned')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->string('honors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('educations');
    }
};

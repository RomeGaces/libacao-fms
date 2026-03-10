<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('eligibility')->nullable();
            $table->integer('rating')->nullable();
            $table->date('exam_date')->nullable();
            $table->string('exam_place')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_validity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('eligibilities');
    }
};

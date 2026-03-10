<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('voluntary_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('organization')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->integer('hours')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('voluntary_works');
    }
};

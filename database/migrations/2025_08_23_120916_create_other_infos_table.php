<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('other_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('skill')->nullable();
            $table->string('recognition')->nullable();
            $table->string('membership')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('other_infos');
    }
};

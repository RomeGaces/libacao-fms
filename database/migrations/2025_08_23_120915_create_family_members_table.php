<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('relationship')->nullable(); // spouse, father, mother, child
            $table->string('name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->string('business_address')->nullable();
            $table->string('telephone_no')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('family_members');
    }
};

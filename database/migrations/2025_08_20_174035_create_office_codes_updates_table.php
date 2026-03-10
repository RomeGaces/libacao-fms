<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_codes_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code_id');
            $table->string('old_office_code');
            $table->string('new_office_code');
            $table->string('old_description');
            $table->string('new_description');
            $table->unsignedBigInteger('updated_by'); // maps to users.employee_id
            $table->date('date_updated');
            $table->timestamps();

            $table->foreign('code_id')->references('id')->on('office_codes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_codes_updates');
    }
};

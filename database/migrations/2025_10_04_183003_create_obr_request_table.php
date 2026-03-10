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
        Schema::create('obr_request', function (Blueprint $table) {
            $table->id();
            
            // Foreign key linking to pao_requests table
            $table->foreignId('request_id')->constrained('pao_requests')->onDelete('cascade');
            
            $table->string('obr_no')->unique();
            $table->text('office_address')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->softDeletes(); // Adds deleted_at column
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obr_request');
    }
};
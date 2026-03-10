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
        Schema::create('obr_objects', function (Blueprint $table) {
            $table->id();

            // Foreign key linking to obr_request table
            $table->foreignId('obr_id')->constrained('obr_request')->onDelete('cascade');

            $table->unsignedBigInteger('object_expenditure_id');
            $table->decimal('amount', 15, 2); // Example: 15 total digits, 2 decimal places
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obr_objects');
    }
};
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
        Schema::create('paper_trail_statuses', function (Blueprint $table) {
            $table->id();

            // Link to your OBR table (adjust table name if needed)
            $table->foreignId('request_id')->constrained('obr_request')->cascadeOnDelete();

            // Paper Trail Set and Step
            $table->foreignId('set_id')->constrained('sets')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('steps')->cascadeOnDelete();

            // New fields for Internal Step tracking
            $table->foreignId('internal_step_id')->constrained('internal_steps')->cascadeOnDelete();
            $table->string('approval_title'); // Cache the readable title from internal_steps

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Ensure one paper trail status per OBR
            $table->unique('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_trail_statuses');
    }
};

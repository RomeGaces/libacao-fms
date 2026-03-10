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
        Schema::dropIfExists('office_codes_updates');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: recreate the table if you want rollback support
        Schema::create('office_codes_updates', function ($table) {
            $table->id();
            $table->unsignedBigInteger('code_id');
            $table->string('old_office_code');
            $table->string('new_office_code');
            $table->string('old_description');
            $table->string('new_description');
            $table->unsignedBigInteger('updated_by');
            $table->date('date_updated');
            $table->timestamps();
        });

    }
};

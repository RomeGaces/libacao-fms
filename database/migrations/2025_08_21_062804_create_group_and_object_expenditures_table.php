<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create parent table first
        Schema::create('group_object_expenditures', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->softDeletes();
            $table->timestamps();
        });

        // Create child table with foreign key
        Schema::create('object_expenditures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('group_object_expenditures')
                  ->onDelete('cascade');
            $table->string('object_expenditure');
            $table->string('account_code');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_expenditures');
        Schema::dropIfExists('group_object_expenditures');
    }
};
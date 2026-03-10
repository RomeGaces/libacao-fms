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
        // pao_requests (parent table)
        Schema::create('pao_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // pao_groups (child of pao_requests)
        Schema::create('pao_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                  ->constrained('pao_requests')
                  ->onDelete('cascade');
             $table->foreignId('group_id')
                ->constrained('group_object_expenditures');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // pao_objects (child of pao_groups)
        Schema::create('pao_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                ->constrained('pao_requests')
                ->onDelete('cascade');
            $table->foreignId('group_id')
                ->constrained('pao_groups')
                ->onDelete('cascade');
            $table->foreignId('object_expenditure_id') // ✅ new column
                ->constrained('object_expenditures')
                ->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pao_objects');
        Schema::dropIfExists('pao_groups');
        Schema::dropIfExists('pao_requests');
    }
};

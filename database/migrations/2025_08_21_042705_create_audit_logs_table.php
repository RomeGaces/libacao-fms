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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation fields
            $table->unsignedBigInteger('auditable_id');
            $table->string('auditable_type');

            // JSON diff of changes
            $table->json('changes')->nullable(); // { "from": { ... }, "to": { ... } }

            // Optional remarks
            $table->text('remarks')->nullable();

            // Actor info
            $table->unsignedBigInteger('updated_by')->nullable(); // FK to users table

            // Timestamp
            $table->timestamp('updated_at')->useCurrent();

            // Index for fast lookup
            $table->index(['auditable_type', 'auditable_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

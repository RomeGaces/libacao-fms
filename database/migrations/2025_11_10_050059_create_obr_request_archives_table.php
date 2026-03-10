<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obr_request_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obr_request_id')->constrained('obr_request')->onDelete('cascade');
            $table->foreignId('archived_by')->constrained('users')->onDelete('cascade');
            $table->text('archive_reason');
            $table->timestamp('archived_at');
            $table->timestamps();

            $table->index('obr_request_id');
            $table->index('archived_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obr_request_archives');
    }
};
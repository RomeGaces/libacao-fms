<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('obr_rejections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->text('rejection_details');
            $table->unsignedBigInteger('rejected_by');
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('obr_request')->onDelete('cascade');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('obr_rejections');
    }
};
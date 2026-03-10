<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('name_extension', 10)->nullable(); // Jr., Sr., III
            $table->date('birth_date')->nullable();
            $table->string('place_of_birth', 255)->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->string('citizenship', 100)->nullable();
            $table->string('height', 10)->nullable();
            $table->string('weight', 10)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('gsis_id_no', 50)->nullable();
            $table->string('pagibig_id_no', 50)->nullable();
            $table->string('philhealth_no', 50)->nullable();
            $table->string('sss_no', 50)->nullable();
            $table->string('tin_no', 50)->nullable();
            $table->string('agency_employee_no', 50)->nullable();
            $table->string('residential_address', 255)->nullable();
            $table->string('residential_zip', 10)->nullable();
            $table->string('permanent_address', 255)->nullable();
            $table->string('permanent_zip', 10)->nullable();
            $table->string('telephone_no', 50)->nullable();
            $table->string('mobile_no', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('ctc_number', 50)->nullable();
            $table->string('ctc_place_of_issuance', 100)->nullable();
            $table->date('ctc_date_of_issuance')->nullable();
            $table->foreignId('plantilla_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('employees');
    }
};

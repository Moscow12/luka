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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUuid('job_title_id')->constrained('job_titles')->cascadeOnDelete();
            $table->foreignUuid('designation_id')->constrained('designations')->cascadeOnDelete();
            $table->foreignUuid('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignUuid('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignUuid('district_id')->constrained('districts')->cascadeOnDelete();
            $table->foreignUuid('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->foreignUuid('vilstreet_id')->constrained('villages')->cascadeOnDelete();
            $table->foreignUuid('workstation_id')->constrained('workstations')->cascadeOnDelete();
            $table->foreignUuid('denomination_id')->constrained('denominations')->cascadeOnDelete();

            // Core info
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_no')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('national_id', 50)->nullable()->unique();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();

            // Employment info
            $table->string('employment_type')->nullable(); // e.g. Full-time, Part-time, Contract
            $table->date('hired_date')->nullable();
            $table->enum('status', ['Active', 'Suspended', 'Terminated', 'Retired'])->default('Active');
            $table->enum('education_level', ['primary', 'diploma', 'certificate', 'degree', 'masters', 'phd'])->nullable();

            // Other info
            $table->string('fpid')->nullable(); // Fingerprint ID or biometric ID
            $table->string('photo')->nullable(); // Path to photo in storage
            $table->string('marital_status', 50)->nullable();
            $table->string('tin_number', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

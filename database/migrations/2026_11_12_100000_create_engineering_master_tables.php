<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eng_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->string('icon', 40)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Section membership drives the assignee drop-downs; is_head marks the Head of Section
        Schema::create('eng_section_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('eng_sections')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_head')->default(false);
            $table->timestamps();

            $table->unique(['section_id', 'employee_id']);
        });

        // emergency_code set => choosing the category forces EMERGENCY priority
        Schema::create('eng_problem_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('eng_sections')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('emergency_code', 10)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['section_id', 'status']);
        });

        Schema::create('eng_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained('departments');
            $table->string('name', 120);
            $table->string('code', 30);
            $table->string('floor', 60)->nullable();
            $table->string('block', 80)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['department_id', 'code']);
        });

        // One row per number series per year; locked while a number is generated
        Schema::create('eng_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('series', 10);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);

            $table->unique(['series', 'year']);
        });

        // Runtime overrides of config/engineering.php (KPI weights, ...)
        Schema::create('eng_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value');
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('eng_oxygen_plants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 80);
            $table->foreignUuid('responsible_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_oxygen_plants');
        Schema::dropIfExists('eng_settings');
        Schema::dropIfExists('eng_sequences');
        Schema::dropIfExists('eng_rooms');
        Schema::dropIfExists('eng_problem_categories');
        Schema::dropIfExists('eng_section_staff');
        Schema::dropIfExists('eng_sections');
    }
};

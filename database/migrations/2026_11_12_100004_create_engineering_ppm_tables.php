<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A device has one schedule per performer (user PPM and engineering PPM differ)
        Schema::create('eng_ppm_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained('eng_equipment')->cascadeOnDelete();
            $table->enum('frequency', ['3_months', '6_months', '12_months', 'running_hours']);
            $table->unsignedInteger('interval_hours')->nullable();
            $table->enum('performer', ['user', 'engineering']);
            $table->foreignUuid('responsible_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('last_done_at')->nullable();
            $table->foreignUuid('last_done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('next_due_at')->nullable();
            $table->string('manufacturer_doc')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['equipment_id', 'performer']);
            $table->index('next_due_at');
        });

        // The checklist template: copied into each instance, so edits never alter past checklists
        Schema::create('eng_ppm_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('eng_ppm_schedules')->cascadeOnDelete();
            $table->unsignedSmallInteger('seq');
            $table->string('description', 300);
            $table->string('expected_value', 100)->nullable();
            $table->boolean('mandatory')->default(true);
            $table->timestamps();
        });

        Schema::create('eng_ppm_consumables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('eng_ppm_schedules')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name', 200);
            $table->string('item_code', 100)->nullable();
            $table->string('uom', 40)->nullable();
            $table->decimal('qty_required', 10, 2);
            $table->timestamps();
        });

        // One blank checklist per PPM occurrence
        Schema::create('eng_ppm_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('eng_ppm_schedules')->cascadeOnDelete();
            $table->foreignUuid('job_id')->nullable()->constrained('eng_jobs')->nullOnDelete();
            $table->date('due_at')->nullable();
            $table->enum('state', ['blank', 'in_progress', 'completed'])->default('blank');
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['schedule_id', 'state']);
        });

        Schema::create('eng_ppm_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('instance_id')->constrained('eng_ppm_instances')->cascadeOnDelete();
            $table->unsignedSmallInteger('seq');
            $table->string('description', 300);
            $table->string('expected_value', 100)->nullable();
            $table->boolean('mandatory')->default(true);
            $table->boolean('done')->default(false);
            $table->boolean('not_applicable')->default(false);
            $table->string('value', 100)->nullable();
            $table->string('comment', 500)->nullable();
            $table->foreignUuid('ticked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ticked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_ppm_entries');
        Schema::dropIfExists('eng_ppm_instances');
        Schema::dropIfExists('eng_ppm_consumables');
        Schema::dropIfExists('eng_ppm_tasks');
        Schema::dropIfExists('eng_ppm_schedules');
    }
};

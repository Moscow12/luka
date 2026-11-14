<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eng_equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->foreignUuid('section_id')->constrained('eng_sections');
            $table->string('code', 60)->unique();
            $table->string('serial_number', 100);
            $table->string('model', 100)->nullable();
            $table->string('manufacturer', 150);
            $table->string('manufacturer_email', 150)->nullable();
            $table->string('manufacturer_phone', 60)->nullable();
            $table->string('manufacturer_postal', 200)->nullable();
            $table->string('supplier', 150)->nullable();
            $table->boolean('under_contract')->default(false);
            $table->date('contract_expiry')->nullable();
            $table->foreignUuid('department_id')->constrained('departments');
            $table->foreignUuid('room_id')->constrained('eng_rooms');
            $table->unsignedInteger('quantity')->default(1);
            $table->date('installation_date');
            $table->enum('condition_received', ['new', 'old'])->default('new');
            $table->unsignedSmallInteger('expected_life_years')->nullable();
            $table->enum('status', ['working', 'partly_defective', 'out_of_service'])->default('working');
            $table->timestamp('status_changed_at')->nullable();
            $table->text('status_note')->nullable();
            $table->string('manual_location', 200);
            $table->string('manual_file')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->foreignUuid('counted_by')->constrained('users');
            $table->date('counted_at');
            // Removal (de-commissioning) keeps the record and its history
            $table->timestamp('removed_at')->nullable();
            $table->string('removal_reason', 200)->nullable();
            $table->string('removal_approved_by', 150)->nullable();
            $table->foreignUuid('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('flagged_for_removal')->default(false);
            $table->timestamps();

            $table->index(['department_id', 'room_id']);
            $table->index('status');
        });

        // Append-only: uptime for any past week is recomputed from this history
        Schema::create('eng_equipment_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained('eng_equipment')->cascadeOnDelete();
            $table->enum('status', ['working', 'partly_defective', 'out_of_service']);
            $table->text('note')->nullable();
            $table->foreignUuid('changed_by')->constrained('users');
            $table->timestamp('created_at')->nullable();

            $table->index(['equipment_id', 'created_at']);
        });

        // Which engineer looks after which room / department. effective_to null = current.
        Schema::create('eng_room_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments');
            $table->foreignUuid('room_id')->nullable()->constrained('eng_rooms')->nullOnDelete();
            $table->dateTime('effective_from');
            $table->timestamp('effective_to')->nullable();
            $table->foreignUuid('assigned_by')->constrained('users');
            $table->timestamps();

            $table->index(['department_id', 'room_id', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_room_staff');
        Schema::dropIfExists('eng_equipment_status_logs');
        Schema::dropIfExists('eng_equipment');
    }
};

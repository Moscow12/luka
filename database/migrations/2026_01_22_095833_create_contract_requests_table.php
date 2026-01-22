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
        Schema::create('contract_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('contract_id')->constrained('employeecontracts')->cascadeOnDelete();

            // Request type: renewal, extension, termination
            $table->enum('request_type', ['renewal', 'extension', 'termination'])->default('renewal');

            // For renewal/extension: proposed new dates
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();

            // For extension: how many months/days to extend
            $table->integer('extension_period')->nullable(); // in months

            // Employee's reason for the request
            $table->text('reason')->nullable();

            // For termination: additional details
            $table->date('last_working_day')->nullable();
            $table->text('handover_notes')->nullable();

            // Request status
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');

            // HR review
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();

            // If approved renewal/extension, link to new contract
            $table->foreignUuid('new_contract_id')->nullable()->constrained('employeecontracts')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_requests');
    }
};

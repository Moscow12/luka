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
        Schema::create('budget_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Request Information
            $table->string('request_number')->unique(); // e.g., BR-2025-001
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUuid('financial_year_id')->constrained('financial_years')->cascadeOnDelete();

            // Status tracking
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'revision_required'])->default('draft');

            // Amounts
            $table->decimal('total_estimated_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->nullable();

            // Justification
            $table->text('justification')->nullable();
            $table->text('director_notes')->nullable();

            // User tracking
            $table->foreignUuid('requested_by')->constrained('users')->cascadeOnDelete(); // HoD
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->cascadeOnDelete(); // CHOP Director

            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_requests');
    }
};

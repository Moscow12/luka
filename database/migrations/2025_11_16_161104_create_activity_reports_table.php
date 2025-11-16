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
        Schema::create('activity_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('activity_id')->constrained('chopactivities')->cascadeOnDelete();
            $table->foreignUuid('reported_by')->constrained('users')->cascadeOnDelete();

            $table->string('report_month'); // e.g., '2024-01' for January 2024
            $table->enum('status', ['completed', 'partially_completed', 'not_completed', 'cancelled'])->default('completed');

            $table->text('completion_notes')->nullable();
            $table->text('challenges_faced')->nullable();

            $table->decimal('amount_spent', 15, 2)->default(0);
            $table->string('payment_proof_document')->nullable(); // Path to payment proof
            $table->string('activity_proof_document')->nullable(); // Path to activity completion proof

            $table->date('actual_completion_date')->nullable();
            $table->integer('beneficiaries_reached')->nullable();
            $table->text('outcomes_achieved')->nullable();

            $table->boolean('is_approved')->default(false);
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one report per activity per month
            $table->unique(['activity_id', 'report_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_reports');
    }
};

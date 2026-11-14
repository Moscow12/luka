<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MRV header: one requisition carries 1-5 jobs
        Schema::create('eng_requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 20)->unique();
            $table->enum('source', ['user_request', 'ppm', 'oxygen'])->default('user_request');
            $table->enum('priority', ['routine', 'emergency'])->default('routine');
            $table->enum('priority_source', ['auto_criterion', 'user_escalated', 'helpdesk_set'])->nullable();
            $table->text('escalation_justification')->nullable();
            $table->foreignUuid('section_id')->constrained('eng_sections');
            $table->foreignUuid('category_id')->nullable()->constrained('eng_problem_categories')->nullOnDelete();
            $table->foreignUuid('requested_by')->constrained('users');
            // Snapshot of the requester at submit time
            $table->string('requester_name', 150);
            $table->string('requester_phone', 30)->nullable();
            $table->string('requester_role', 150)->nullable();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('room_id')->nullable()->constrained('eng_rooms')->nullOnDelete();
            $table->string('location_text', 200)->nullable();
            $table->dateTime('requested_at');
            $table->timestamps();

            $table->index('requested_at');
            $table->index(['requested_by', 'requested_at']);
            $table->index('department_id');
        });

        // One job per requisition line; carries its current assignment and closure
        Schema::create('eng_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requisition_id')->constrained('eng_requisitions')->cascadeOnDelete();
            $table->unsignedTinyInteger('seq');
            $table->string('number', 24)->unique();
            $table->foreignUuid('equipment_id')->nullable()->constrained('eng_equipment')->nullOnDelete();
            $table->string('equipment_text', 200)->nullable();
            $table->boolean('not_in_inventory')->default(false);
            $table->text('problem_description');
            $table->enum('status', [
                'submitted', 'assigned', 'in_progress', 'awaiting_spares', 'assistance_needed',
                'rework', 'completed', 'closed', 'beyond_repair',
            ])->default('submitted');
            $table->boolean('is_rework')->default(false);
            $table->unsignedSmallInteger('rework_count')->default(0);

            // Current assignment
            $table->enum('type_of_work', ['ppm', 'breakdown', 'installation', 'inspection', 'other'])->nullable();
            $table->foreignUuid('section_id')->constrained('eng_sections');
            $table->foreignUuid('engineer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            // Start of the TAT clock; survives re-assignment
            $table->timestamp('original_assigned_at')->nullable();
            $table->unsignedSmallInteger('days_to_be_done')->nullable();
            $table->text('assignment_note')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // Spare parts & procurement tracker
            $table->timestamp('spare_required_at')->nullable();
            $table->timestamp('procurement_order_at')->nullable();
            $table->timestamp('client_informed_at')->nullable();
            $table->boolean('no_spares_consumed')->default(false);

            // Closure
            $table->enum('final_status', ['job_complete', 'beyond_repair', 'assistance_needed'])->nullable();
            $table->text('assistance_explanation')->nullable();
            $table->text('recommendation')->nullable();
            $table->foreignUuid('signed_out_by')->nullable()->constrained('users')->nullOnDelete();
            // End of the TAT clock
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['requisition_id', 'seq']);
            $table->index('status');
            $table->index(['engineer_id', 'status']);
            $table->index(['section_id', 'status']);
            $table->index('original_assigned_at');
            $table->index('completed_at');
            $table->index('equipment_id');
        });

        Schema::create('eng_job_assistants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['job_id', 'employee_id']);
        });

        // Append-only assignment history
        Schema::create('eng_job_assignment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->enum('action', ['assigned', 'reassigned', 'rework', 'returned_to_queue']);
            $table->foreignUuid('from_engineer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('to_engineer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('section_id')->nullable()->constrained('eng_sections')->nullOnDelete();
            $table->foreignUuid('actor_id')->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Append-only thread: engineer job notes and requester follow-up comments
        Schema::create('eng_job_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->enum('kind', ['engineer', 'requester', 'system'])->default('engineer');
            $table->text('body');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamp('created_at')->nullable();

            $table->index(['job_id', 'created_at']);
        });

        Schema::create('eng_safety_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->enum('test_type', ['electrical', 'functional', 'visual']);
            $table->boolean('completed')->default(false);
            $table->boolean('not_applicable')->default(false);
            $table->string('comment', 500)->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'test_type']);
        });

        // Every rating is kept; the latest non-superseded one counts for KPIs
        Schema::create('eng_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->enum('rating', ['poor_service', 'below_expectation', 'met_expectation', 'above_expectation', 'excellent']);
            $table->unsignedTinyInteger('score_pct');
            $table->text('comment')->nullable();
            $table->foreignUuid('given_by')->constrained('users');
            $table->boolean('resent_for_rework')->default(false);
            $table->boolean('superseded')->default(false);
            $table->timestamps();

            $table->index(['job_id', 'superseded']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_feedback');
        Schema::dropIfExists('eng_safety_tests');
        Schema::dropIfExists('eng_job_notes');
        Schema::dropIfExists('eng_job_assignment_logs');
        Schema::dropIfExists('eng_job_assistants');
        Schema::dropIfExists('eng_jobs');
        Schema::dropIfExists('eng_requisitions');
    }
};

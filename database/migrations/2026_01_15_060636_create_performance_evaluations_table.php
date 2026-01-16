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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_plan_id')->constrained('employee_plans')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('self_score', 5, 2)->nullable();
            $table->decimal('supervisor_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('supervisor_comments')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->integer('current_approval_level')->default(0);
            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status'], 'perf_eval_emp_status_idx');
        });

        // Performance evaluation approvals tracking table
        Schema::create('performance_evaluation_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('performance_evaluation_id');
            $table->uuid('approval_level_id');
            $table->uuid('approved_by');
            $table->enum('status', ['approved', 'rejected'])->default('approved');
            $table->text('remarks')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();

            // Foreign keys with short constraint names
            $table->foreign('performance_evaluation_id', 'perf_eval_appr_eval_fk')
                ->references('id')->on('performance_evaluations')->cascadeOnDelete();
            $table->foreign('approval_level_id', 'perf_eval_appr_level_fk')
                ->references('id')->on('approvallevels')->cascadeOnDelete();
            $table->foreign('approved_by', 'perf_eval_appr_by_fk')
                ->references('id')->on('employees')->cascadeOnDelete();

            $table->index(['performance_evaluation_id', 'approval_level_id'], 'perf_eval_appr_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluation_approvals');
        Schema::dropIfExists('performance_evaluations');
    }
};

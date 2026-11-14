<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 5-Why root cause analysis, 1:1 with a job
        Schema::create('eng_rcas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 20)->unique();
            $table->foreignUuid('job_id')->unique()->constrained('eng_jobs')->cascadeOnDelete();
            $table->date('session_date')->nullable();
            $table->text('indication_of_problem')->nullable();
            $table->text('client_observations')->nullable();
            $table->text('actions_taken')->nullable();
            $table->json('probable_causes')->nullable();
            $table->text('actual_problem')->nullable();
            $table->string('probable_cause_selected', 300)->nullable();
            $table->json('participants')->nullable();
            // [{answer, evidence}, ...] - each question is phrased from the previous answer
            $table->json('whys')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('early_stop_justification')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->foreignUuid('responsible_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('action_target_date')->nullable();
            $table->timestamp('action_done_at')->nullable();
            $table->foreignUuid('facilitator_id')->constrained('users');
            $table->enum('status', ['draft', 'completed', 'closed'])->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Cylinder orders: the delivery itself is tracked as a job
        Schema::create('eng_oxygen_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->unique()->constrained('eng_jobs')->cascadeOnDelete();
            $table->string('cylinder_type', 60);
            $table->unsignedInteger('qty_ordered');
            $table->unsignedInteger('qty_delivered')->nullable();
            $table->foreignUuid('delivered_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->string('received_by', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('eng_oxygen_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plant_id')->constrained('eng_oxygen_plants')->cascadeOnDelete();
            $table->dateTime('reading_at');
            $table->decimal('purity_pct', 5, 2);
            $table->foreignUuid('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['plant_id', 'reading_at']);
        });

        Schema::create('eng_five_s_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('week_start');
            $table->decimal('score_pct', 5, 2);
            $table->foreignUuid('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['employee_id', 'week_start']);
        });

        // Per-equipment narrative for management, raised from a job
        Schema::create('eng_technical_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->unique()->constrained('eng_jobs')->cascadeOnDelete();
            $table->date('date_reported');
            $table->text('problem_background')->nullable();
            $table->text('work_done')->nullable();
            $table->text('equipment_requirement')->nullable();
            $table->text('engineer_needs')->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('new_purchase_cost', 15, 2)->nullable();
            $table->decimal('external_expert_cost', 15, 2)->nullable();
            $table->text('comment_to_management')->nullable();
            $table->foreignUuid('reported_by')->constrained('users');
            $table->string('reporter_role', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_technical_reports');
        Schema::dropIfExists('eng_five_s_scores');
        Schema::dropIfExists('eng_oxygen_readings');
        Schema::dropIfExists('eng_oxygen_orders');
        Schema::dropIfExists('eng_rcas');
    }
};

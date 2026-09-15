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
        Schema::create('employee_assigned_duties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('duty_name');
            $table->text('description')->nullable();
            $table->enum('kpi_type', ['qualitative', 'quantitative'])->default('quantitative');
            $table->enum('measurement_type', ['numeric', 'boolean', 'percentage', 'rating_scale'])->default('numeric');
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('target_unit')->nullable();
            $table->decimal('min_acceptable', 15, 2)->nullable();
            $table->decimal('max_possible', 15, 2)->nullable();
            $table->integer('rating_scale_max')->nullable();
            $table->text('scoring_criteria')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            // Achievement tracking
            $table->decimal('actual_achievement', 15, 2)->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('achievement_notes')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_assigned_duties');
    }
};

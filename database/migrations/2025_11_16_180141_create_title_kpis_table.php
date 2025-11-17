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
        Schema::create('title_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_title_id')->constrained('jobtitles')->cascadeOnDelete();
            $table->string('kpi_name');
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
            $table->text('performance_indicators')->nullable(); // JSON or text
            $table->integer('display_order')->default(0);
            $table->boolean('is_mandatory')->default(true); // All employees with this title must meet this
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('title_kpis');
    }
};

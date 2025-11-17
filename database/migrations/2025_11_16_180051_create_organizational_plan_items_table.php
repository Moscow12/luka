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
        Schema::create('organizational_plan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organizational_plan_id')->constrained('organizational_plans')->cascadeOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->enum('kpi_type', ['qualitative', 'quantitative'])->default('quantitative');
            $table->enum('measurement_type', ['numeric', 'boolean', 'percentage', 'rating_scale'])->default('numeric');
            $table->decimal('weight', 5, 2)->default(0); // Percentage weight of this item
            $table->decimal('target_value', 15, 2)->nullable(); // Target number/value
            $table->string('target_unit')->nullable(); // Unit of measurement (e.g., %, count, TZS)
            $table->decimal('min_acceptable', 15, 2)->nullable(); // Minimum acceptable value
            $table->decimal('max_possible', 15, 2)->nullable(); // Maximum possible value
            $table->integer('rating_scale_max')->nullable(); // For rating scale (e.g., 1-5, 1-10)
            $table->text('scoring_criteria')->nullable(); // JSON or text describing how to score
            $table->integer('display_order')->default(0);
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
        Schema::dropIfExists('organizational_plan_items');
    }
};

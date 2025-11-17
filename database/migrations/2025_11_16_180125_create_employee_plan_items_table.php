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
        Schema::create('employee_plan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_plan_id')->constrained('employee_plans')->cascadeOnDelete();
            $table->foreignUuid('department_plan_item_id')->nullable()->constrained('department_plan_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            // Inherit from department/org plan but allow adjustment
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('target_unit')->nullable();
            $table->decimal('min_acceptable', 15, 2)->nullable();
            $table->decimal('max_possible', 15, 2)->nullable();
            // Achievement tracking
            $table->decimal('actual_achievement', 15, 2)->nullable();
            $table->decimal('score', 5, 2)->nullable(); // Score achieved (0-100)
            $table->text('achievement_notes')->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('supervisor_comments')->nullable();
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
        Schema::dropIfExists('employee_plan_items');
    }
};

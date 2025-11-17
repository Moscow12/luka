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
        Schema::create('department_plan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_plan_id')->constrained('department_plans')->cascadeOnDelete();
            $table->foreignUuid('organizational_plan_item_id')->constrained('organizational_plan_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            // Inherit from org plan item but allow adjustment
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('target_unit')->nullable();
            $table->decimal('min_acceptable', 15, 2)->nullable();
            $table->decimal('max_possible', 15, 2)->nullable();
            $table->text('department_specific_notes')->nullable();
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
        Schema::dropIfExists('department_plan_items');
    }
};

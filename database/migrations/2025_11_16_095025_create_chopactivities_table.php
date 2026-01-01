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
        Schema::create('chopactivities', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('planned_activity');
            $table->string('actual_activity')->nullable();
            $table->string('description')->nullable();
            $table->string('status');
            $table->decimal('planned_amount', 8, 2)->nullable();
            $table->decimal('actual_amount', 8, 2)->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_planned')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->text('expected_outcome')->nullable();
            $table->string('expected_outcome_date')->nullable();
            $table->enum('activity_type', ['expenditure', 'revenue'])->default('expenditure');
            $table->enum('frequence_monitoring', ['weekly',    'monthly',    'bimonthly',    'quarterly',    'quadrimonthly',    'biannual',    'annual'])->default('monthly');
            $table->foreignUuid('source_id')->constrained('sourceoffunds')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('chopcategoryareas')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('financial_year_id')->constrained('financial_years')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chopactivities');
    }
};

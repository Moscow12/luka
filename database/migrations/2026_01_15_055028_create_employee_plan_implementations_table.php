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
        Schema::create('employee_plan_implementations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_plan_item_id')->constrained('employee_plan_items')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('implementation_date');
            $table->string('activity_title');
            $table->text('activity_description')->nullable();
            $table->decimal('quantity_achieved', 15, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('evidence')->nullable();
            $table->text('challenges')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('supervisor_remarks')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'implementation_date'], 'emp_impl_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_plan_implementations');
    }
};

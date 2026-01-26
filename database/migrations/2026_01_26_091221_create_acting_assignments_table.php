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
        Schema::create('acting_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('leave_request_id')->constrained('employeeleaves')->cascadeOnDelete();
            $table->foreignUuid('employee_on_leave_id')->constrained('employees')->cascadeOnDelete(); // Person going on leave
            $table->foreignUuid('acting_employee_id')->constrained('employees')->cascadeOnDelete(); // Person acting
            $table->foreignUuid('acting_designation_id')->nullable()->constrained('designations')->nullOnDelete(); // Acting position
            $table->foreignUuid('acting_department_id')->nullable()->constrained('departments')->nullOnDelete(); // Acting department
            $table->date('start_date');
            $table->date('end_date');
            $table->text('responsibilities')->nullable(); // Specific duties to be performed
            $table->text('notes')->nullable(); // Additional notes
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'completed', 'cancelled'])->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('notify_acting_employee')->default(true);
            $table->boolean('grant_system_access')->default(false); // Whether to grant temporary system access
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['acting_employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acting_assignments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number', 20)->unique();
            $table->string('subject', 150);
            $table->longText('description');
            $table->foreignUuid('category_id')->constrained('ticket_categories');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignUuid('department_id')->constrained('departments');
            $table->foreignUuid('assignee_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('employee_assigned_duty_id')->nullable()->constrained('employee_assigned_duties')->nullOnDelete();
            $table->date('due_date');
            $table->enum('visibility', ['participants', 'department'])->default('participants');
            $table->enum('status', [
                'open', 'assigned', 'accepted', 'in_progress', 'pending_response',
                'resolved', 'closed', 'reopened', 'cancelled',
            ])->default('open');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignUuid('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('reopen_count')->default(0);
            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->text('closing_remarks')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['department_id', 'status']);
            $table->index(['assignee_employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

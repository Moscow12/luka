<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only history of every (re)assignment
        Schema::create('ticket_assignment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignUuid('previous_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('new_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('previous_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('new_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('acted_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assignment_logs');
    }
};

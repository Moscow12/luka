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
        Schema::create('certificates_of_service', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('certificate_number')->unique();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('contract_id')->constrained('employeecontracts')->cascadeOnDelete();
            $table->foreignUuid('contract_request_id')->nullable()->constrained('contract_requests')->nullOnDelete();

            // Certificate details
            $table->enum('separation_type', ['termination', 'end_of_contract', 'resignation', 'retirement', 'other'])->default('end_of_contract');
            $table->foreignUuid('termination_reason_id')->nullable()->constrained('termination_reasons')->nullOnDelete();
            $table->date('service_start_date');
            $table->date('service_end_date');
            $table->string('position_held');
            $table->string('department');
            $table->text('duties_performed')->nullable();
            $table->text('additional_remarks')->nullable();

            // Declaration text (can be customized per certificate)
            $table->text('declaration_text')->nullable();

            // Approval workflow
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'printed', 'cancelled'])->default('draft');
            $table->foreignUuid('prepared_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comments')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->integer('print_count')->default(0);

            // Workstation for letterhead/logo
            $table->foreignUuid('workstation_id')->nullable()->constrained('workstations')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates_of_service');
    }
};

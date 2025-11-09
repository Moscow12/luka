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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->foreignUuid('contract_allowance_id')->nullable()->constrained('contract_allowances')->onDelete('set null');
            $table->foreignUuid('contract_deduction_id')->nullable()->constrained('contract_deductions')->onDelete('set null');
            $table->string('name'); // e.g. "PAYE", "NSSF", "Loan", "Bonus"
            $table->enum('type', ['allowance', 'deduction']);
            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignUuid('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};

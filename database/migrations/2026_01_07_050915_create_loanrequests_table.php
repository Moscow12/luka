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
        Schema::create('loanrequests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->decimal('amount', 15, 2);
            $table->date('request_date');
            $table->decimal('interest_rate', 5, 2);
            $table->enum('interest_type', ['flat', 'reducing_balance'])->default('reducing_balance');
            $table->integer('repayment_period_months');
            $table->text('reason');
            $table->text('remarks')->nullable()->comment('For approval or rejection remarks');
            $table->string('status')->default('pending')->comment('pending,under_review, approved, rejected, disbursed,active, closed');
            $table->decimal('installment_amount', 15, 2)->default(0.00);
            $table->decimal('approved_amount', 15, 2)->default(0.00)->nullable();
            $table->date('approval_date')->nullable();
            $table->foreignUuid('loan_item_id')->constrained('loan_items')->cascadeOnDelete();
            $table->foreignUuid('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('applicant_type', ['customer', 'staff', 'vendor'])->default('staff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loanrequests');
    }
};

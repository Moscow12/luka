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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status')->default('paid');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->foreignUuid('loanrequest_id')->constrained('loanrequests')->cascadeOnDelete();
            $table->foreignUuid('recorded_by')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};

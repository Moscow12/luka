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
        Schema::create('employeeallowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('allowance_id')->constrained('allowances')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();            
            $table->decimal('allowance_amount', 12, 2);
            $table->foreignUuid('salary_id')->constrained('employeesalaries')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeeallowances');
    }
};

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
        Schema::create('employeerosters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->onDelete('cascade');
            $table->date('roster_date'); // The date employee is scheduled
            $table->foreignUuid('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->string('shift_type')->nullable(); // shift, holiday, etc.
            $table->string('status')->default('scheduled'); // scheduled, off, leave, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeerosters');
    }
};

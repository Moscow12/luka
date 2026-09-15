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
        Schema::create('employeequalifications', function (Blueprint $table) {
            //Eductation_level, Institution, start_date, end_date, attachment, comments, employee_id, added_by
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();            
            $table->string('education_level');
            $table->string('institution');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('attachment');
            $table->text('comments');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeequalifications');
    }
};

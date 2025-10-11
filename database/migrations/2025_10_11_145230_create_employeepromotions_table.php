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
        // title_id, workstation_id, department_id, start_date, attachment, comments, employee_id, added_by
        Schema::create('employeepromotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('title_id')->constrained('jobtitles')->cascadeOnDelete();
            $table->foreignUuid('workstation_id')->constrained('workstations')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();            
            $table->date('start_date');
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
        Schema::dropIfExists('employeepromotions');
    }
};

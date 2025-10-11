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
        Schema::create('employeecontracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            //  employee_contact(workstation_id, contract_type, start_date, expire_date, position_id,  expirenotification, attachment)
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('workstation_id')->constrained('workstations')->cascadeOnDelete();
            $table->foreignUuid('position_id')->constrained('jobtitles')->cascadeOnDelete();
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('expire_date');
            $table->boolean('expirenotification')->default(false);
            $table->string('notify_time')->nullable();
            $table->string('attachment');
            $table->text('description')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeecontracts');
    }
};

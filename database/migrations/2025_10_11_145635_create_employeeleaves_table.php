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
        Schema::create('employeeleaves', function (Blueprint $table) {
            //leave_id, employee_id, start_date, end_date, days, travel_to, othercontact, comments, added_by
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('leave_id')->constrained('leaves')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();            
            $table->date('start_date');
            $table->date('end_date');
            $table->string('days');
            $table->string('travel_to');
            $table->string('othercontact');
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
        Schema::dropIfExists('employeeleaves');
    }
};

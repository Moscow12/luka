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
        Schema::create('employeedependants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('name');
            $table->string('relationship'); // e.g., Spouse, Child, Parent, etc.
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_next_of_kin')->default(false);
            $table->foreignUuid('added_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeedependants');
    }
};

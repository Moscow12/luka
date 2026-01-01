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
        Schema::create('salary_scales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pay_grade');
            $table->string('job_title');
            $table->decimal('minimum_salary', 12, 2);
            $table->decimal('mid_point_salary', 12, 2)->nullable();
            $table->decimal('maximum_salary', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();   
            $table->timestamps();

            $table->index(['pay_grade', 'is_active']);
            $table->index('job_title');
            $table->index(['minimum_salary', 'maximum_salary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_scales');
    }
};

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
        Schema::create('allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('Pay_Grade');
            $table->string('Job_Title');
            $table->decimal('Minimum_Salary', 12, 2);
            $table->decimal('Mid_Point_Salary', 12, 2)->nullable();
            $table->decimal('Maximum_Salary', 12, 2);
            $table->text('Description')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};

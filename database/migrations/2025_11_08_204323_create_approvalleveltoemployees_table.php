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
        Schema::create('approvalleveltoemployees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('approval_level_id')->constrained('approvallevels')->onDelete('cascade');
            $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvalleveltoemployees');
    }
};

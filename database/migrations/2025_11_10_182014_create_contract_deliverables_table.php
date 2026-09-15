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
        Schema::create('contract_deliverables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('deliverable_name');
            $table->string('deliverable_number')->nullable();
            $table->string('deliverable_type');
            $table->string('description')->nullable();
            $table->string('deliverable');
            $table->string('kpi')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'under_review'])->default('pending');
            $table->date('due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_deliverables');
    }
};

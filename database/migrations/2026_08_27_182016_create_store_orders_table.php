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
        Schema::create('store_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Order Information
            $table->string('order_number')->unique(); // e.g., SO-2026-0001
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();

            // Status tracking. Approval / issuing / procurement routing is handled downstream.
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');

            $table->text('order_description')->nullable();

            // User tracking
            $table->foreignUuid('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_orders');
    }
};

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
        Schema::create('local_purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('lpo_number')->unique();
            $table->foreignUuid('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignUuid('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('generated_at');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_purchase_orders');
    }
};

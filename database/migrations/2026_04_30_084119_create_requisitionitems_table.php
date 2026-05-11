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
        Schema::create('requisitionitems', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requisition_id')->constrained('requisitions')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('remarks')->nullable();
            $table->boolean('require_purchase_order')->default(false)->nullable();
            $table->boolean('is_approved')->default(false)->nullable();
            $table->boolean('is_rejected')->default(false)->nullable();
            $table->string('progress')->default('pending')->comment('pending, approved, rejected', 'procurement');
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitionitems');
    }
};

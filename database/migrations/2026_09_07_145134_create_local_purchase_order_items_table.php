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
        Schema::create('local_purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('local_purchase_order_id')->constrained('local_purchase_orders')->cascadeOnDelete();
            $table->foreignUuid('purchase_requisition_item_id')->constrained('purchase_requisition_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->foreignUuid('supplier_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('quantity', 10, 2);
            $table->string('quotation1')->nullable();
            $table->string('quotation2')->nullable();
            $table->string('quotation3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_purchase_order_items');
    }
};

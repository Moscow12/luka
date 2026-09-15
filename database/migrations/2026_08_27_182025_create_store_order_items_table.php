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
        Schema::create('store_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('store_order_id')->constrained('store_orders')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();

            $table->decimal('quantity', 10, 2);
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_order_items');
    }
};

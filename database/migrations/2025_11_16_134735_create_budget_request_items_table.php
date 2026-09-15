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
        Schema::create('budget_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('budget_request_id')->constrained('budget_requests')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('chopcategoryareas')->cascadeOnDelete();

            // HoD Request
            $table->decimal('requested_quantity', 10, 2);
            $table->decimal('requested_price', 15, 2)->nullable();
            $table->text('justification')->nullable();

            // Director Review/Modification
            $table->decimal('approved_quantity', 10, 2)->nullable();
            $table->decimal('approved_price', 15, 2)->nullable();
            $table->text('director_comment')->nullable();

            // Calculated totals
            $table->decimal('requested_total', 15, 2)->storedAs('requested_quantity * COALESCE(requested_price, 0)');
            $table->decimal('approved_total', 15, 2)->nullable()->storedAs('COALESCE(approved_quantity, 0) * COALESCE(approved_price, 0)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_request_items');
    }
};

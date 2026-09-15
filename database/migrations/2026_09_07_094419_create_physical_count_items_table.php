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
        Schema::create('physical_count_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('physical_count_id')->constrained('physical_counts')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->integer('system_qty')->default(0);
            $table->integer('counted_qty')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_count_items');
    }
};

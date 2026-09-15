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
        Schema::create('activityitems', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('activity_id')->constrained('chopactivities')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->decimal('quantity', 8, 2)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activityitems');
    }
};

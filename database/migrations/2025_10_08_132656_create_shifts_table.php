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
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('count_early')->nullable(); // e.g., "00:15" for 15 minutes early
            $table->string('count_late')->nullable(); // e.g., "00:15" for 15 minutes late
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};

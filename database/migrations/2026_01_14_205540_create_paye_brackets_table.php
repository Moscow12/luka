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
        Schema::create('paye_brackets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('rate', 5, 2);
            $table->decimal('fixed_amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['min_amount', 'max_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paye_brackets');
    }
};

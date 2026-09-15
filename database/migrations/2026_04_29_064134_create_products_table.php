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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code', 100)->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('cost_price', 15, 2)->nullable(0);
            $table->decimal('selling_price', 15, 2)->nullable(0);
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_serialized')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->integer('reorder_level')->default(0);
            $table->integer('useful_life')->default(0)->nullable();
            $table->string('unit')->nullable();
            $table->enum('type', ['good', 'asset', 'service', 'work', 'mixed'])->default('mixed');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('product_category_id')->constrained('productcategories')->cascadeOnDelete();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        // Existing store_order_items reference chopitems, which is unrelated to the
        // products catalog. There is no valid mapping between the two, so this
        // rebuilds the table pointing item_id at products instead.
        Schema::create('store_order_items_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_order_id')->constrained('store_orders')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('store_order_items');

        Schema::rename('store_order_items_temp', 'store_order_items');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        Schema::create('store_order_items_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_order_id')->constrained('store_orders')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('store_order_items');

        Schema::rename('store_order_items_temp', 'store_order_items');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
};

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

        // Existing rows reference chopitems, which is unrelated to the products
        // catalog. There is no valid mapping between the two, so this rebuilds
        // both tables pointing item_id at products instead.
        Schema::create('stock_ledger_controls_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('department_store_id')->constrained('department_stores')->cascadeOnDelete();
            $table->foreignUuid('internal_source')->nullable()->constrained('department_stores')->nullOnDelete();
            $table->foreignUuid('external_source')->nullable()->constrained('department_stores')->nullOnDelete();
            $table->integer('document_number');
            $table->integer('pre_balance')->default(0);
            $table->integer('post_balance')->default(0);
            $table->integer('qty')->virtualAs('(post_balance - pre_balance)');
            $table->string('movement_type')
                ->comment('Open Balance, Dispensed, Issue Note, GRN Against Issue Note, Without Purchase, Return, From External, Issue Note Manual, Received From Issue Note Manual, ADJUSTMENT, Return Inward, Return Outward');
            $table->date('movement_date');
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::dropIfExists('stock_ledger_controls');
        Schema::rename('stock_ledger_controls_temp', 'stock_ledger_controls');

        Schema::create('physical_count_items_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('physical_count_id')->constrained('physical_counts')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('products')->cascadeOnDelete();
            $table->integer('system_qty')->default(0);
            $table->integer('counted_qty')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('physical_count_items');
        Schema::rename('physical_count_items_temp', 'physical_count_items');

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

        Schema::create('stock_ledger_controls_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->foreignUuid('department_store_id')->constrained('department_stores')->cascadeOnDelete();
            $table->foreignUuid('internal_source')->nullable()->constrained('department_stores')->nullOnDelete();
            $table->foreignUuid('external_source')->nullable()->constrained('department_stores')->nullOnDelete();
            $table->integer('document_number');
            $table->integer('pre_balance')->default(0);
            $table->integer('post_balance')->default(0);
            $table->integer('qty')->virtualAs('(post_balance - pre_balance)');
            $table->string('movement_type')
                ->comment('Open Balance, Dispensed, Issue Note, GRN Against Issue Note, Without Purchase, Return, From External, Issue Note Manual, Received From Issue Note Manual, ADJUSTMENT, Return Inward, Return Outward');
            $table->date('movement_date');
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::dropIfExists('stock_ledger_controls');
        Schema::rename('stock_ledger_controls_temp', 'stock_ledger_controls');

        Schema::create('physical_count_items_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('physical_count_id')->constrained('physical_counts')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('chopitems')->cascadeOnDelete();
            $table->integer('system_qty')->default(0);
            $table->integer('counted_qty')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('physical_count_items');
        Schema::rename('physical_count_items_temp', 'physical_count_items');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
};

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
        Schema::create('stock_ledger_controls', function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_controls');
    }
};

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
        Schema::table('employee_assigned_duties', function (Blueprint $table) {
            $table->foreignUuid('store_order_id')->nullable()->after('employee_id')
                ->constrained('store_orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_assigned_duties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_order_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A register row is either one numbered unit (Bed 1, Bed 2: quantity 1) or a lot of identical
 * items (20 beds). purchase_cost is the cost of the whole row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assetregistries', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('assetregistries', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};

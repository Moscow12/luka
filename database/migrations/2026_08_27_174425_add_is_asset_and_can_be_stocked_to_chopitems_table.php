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
        Schema::table('chopitems', function (Blueprint $table) {
            $table->boolean('is_asset')->default(false)->after('is_active');
            $table->boolean('can_be_stocked')->default(true)->after('is_asset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chopitems', function (Blueprint $table) {
            $table->dropColumn(['is_asset', 'can_be_stocked']);
        });
    }
};

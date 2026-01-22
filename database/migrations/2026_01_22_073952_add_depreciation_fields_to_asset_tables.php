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
        // Add to assetclasses table
        Schema::table('assetclasses', function (Blueprint $table) {
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance'])->default('straight_line')->after('depreciation');
            $table->integer('useful_life_years')->nullable()->after('depreciation_method');
            $table->decimal('depreciation_rate', 5, 2)->nullable()->after('useful_life_years');
        });

        // Add to assetregistries table
        Schema::table('assetregistries', function (Blueprint $table) {
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance'])->nullable()->after('depreciation_period');
            $table->integer('useful_life_years')->nullable()->after('depreciation_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assetclasses', function (Blueprint $table) {
            $table->dropColumn(['depreciation_method', 'useful_life_years', 'depreciation_rate']);
        });

        Schema::table('assetregistries', function (Blueprint $table) {
            $table->dropColumn(['depreciation_method', 'useful_life_years']);
        });
    }
};

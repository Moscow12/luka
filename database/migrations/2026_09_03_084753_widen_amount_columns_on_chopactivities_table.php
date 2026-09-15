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
        Schema::table('chopactivities', function (Blueprint $table) {
            $table->decimal('planned_amount', 18, 2)->nullable()->change();
            $table->decimal('actual_amount', 18, 2)->nullable()->change();
        });

        Schema::table('activityitems', function (Blueprint $table) {
            $table->decimal('price', 18, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chopactivities', function (Blueprint $table) {
            $table->decimal('planned_amount', 8, 2)->nullable()->change();
            $table->decimal('actual_amount', 8, 2)->nullable()->change();
        });

        Schema::table('activityitems', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable()->change();
        });
    }
};

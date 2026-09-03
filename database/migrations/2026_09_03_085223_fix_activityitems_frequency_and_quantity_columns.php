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
        Schema::table('activityitems', function (Blueprint $table) {
            $table->string('frequency')->nullable()->default(null)->change();
            $table->decimal('quantity', 18, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activityitems', function (Blueprint $table) {
            $table->string('frequency')->nullable(false)->change();
            $table->string('quantity')->nullable()->change();
        });
    }
};

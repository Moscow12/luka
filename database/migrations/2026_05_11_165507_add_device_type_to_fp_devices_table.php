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
        Schema::table('fp_devices', function (Blueprint $table) {
            $table->enum('device_type', ['zkteco', 'anviz'])->default('zkteco')->after('name');
            $table->integer('port')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fp_devices', function (Blueprint $table) {
            $table->dropColumn('device_type');
            $table->integer('port')->default(4370)->change();
        });
    }
};

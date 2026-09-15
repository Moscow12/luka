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
            $table->string('username')->nullable()->after('port')->comment('Username for Anviz devices');
            $table->string('password')->nullable()->after('username')->comment('Password for Anviz devices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fp_devices', function (Blueprint $table) {
            $table->dropColumn(['username', 'password']);
        });
    }
};

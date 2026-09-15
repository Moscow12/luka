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
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'suspended',
                'terminated',
                'retired',
                'contract_ended',
                'resigned',
                'deceased',
                'transferred',
                'study_leave',
                'absconded',
                'other'
            ])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Suspended', 'Terminated', 'Retired'])->default('Active')->change();
        });
    }
};

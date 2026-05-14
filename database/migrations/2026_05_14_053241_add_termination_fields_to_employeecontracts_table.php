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
        Schema::table('employeecontracts', function (Blueprint $table) {
            $table->enum('termination_reason', [
                'contract_ended',
                'resigned',
                'terminated',
                'deceased',
                'transferred',
                'retired',
                'study_leave',
                'absconded',
                'other'
            ])->nullable()->after('status');
            $table->date('termination_date')->nullable()->after('termination_reason');
            $table->text('termination_notes')->nullable()->after('termination_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employeecontracts', function (Blueprint $table) {
            $table->dropColumn(['termination_reason', 'termination_date', 'termination_notes']);
        });
    }
};

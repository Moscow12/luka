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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('taxable_salary', 15, 2)->default(0)->after('gross_salary');
            $table->decimal('mafao_deductions', 15, 2)->default(0)->after('taxable_salary');
            $table->decimal('paye_tax', 15, 2)->default(0)->after('mafao_deductions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['taxable_salary', 'mafao_deductions', 'paye_tax']);
        });
    }
};

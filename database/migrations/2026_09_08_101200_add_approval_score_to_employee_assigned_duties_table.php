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
        Schema::table('employee_assigned_duties', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_score')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_assigned_duties', function (Blueprint $table) {
            $table->dropColumn('approval_score');
        });
    }
};

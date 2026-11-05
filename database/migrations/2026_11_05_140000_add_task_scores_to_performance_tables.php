<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_plan_implementations', function (Blueprint $table) {
            $table->unsignedTinyInteger('verification_score')->nullable()->after('supervisor_remarks');
        });

        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->decimal('task_score', 5, 2)->nullable()->after('supervisor_score');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plan_implementations', function (Blueprint $table) {
            $table->dropColumn('verification_score');
        });

        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn('task_score');
        });
    }
};

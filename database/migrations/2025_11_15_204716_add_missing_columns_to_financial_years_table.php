<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_years', function (Blueprint $table) {
            // Add missing columns
            $table->string('name')->nullable()->after('id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('is_current');
            $table->text('description')->nullable()->after('status');
        });

        // Update existing records to have a name
        DB::table('financial_years')->whereNull('name')->update([
            'name' => DB::raw("'FY ' || substr(start_date, 1, 4) || '-' || substr(end_date, 1, 4)"),
            'status' => 'active'
        ]);

        // Make name unique after populating
        Schema::table('financial_years', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_years', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn(['name', 'status', 'description']);
        });
    }
};

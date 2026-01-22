<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the contract_type enum to include new values
        DB::statement("ALTER TABLE employeecontracts MODIFY COLUMN contract_type ENUM('permanent', 'temporary', 'part_time', 'probation', 'internship', 'consultancy', 'other') DEFAULT 'permanent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE employeecontracts MODIFY COLUMN contract_type ENUM('permanent', 'temporary', 'part_time') DEFAULT 'permanent'");
    }
};

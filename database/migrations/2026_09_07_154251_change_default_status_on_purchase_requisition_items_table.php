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
        DB::statement(
            'ALTER TABLE purchase_requisition_items MODIFY COLUMN status '
            ."ENUM('pending', 'approved', 'active', 'rejected') "
            ."NOT NULL DEFAULT 'active'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE purchase_requisition_items MODIFY COLUMN status '
            ."ENUM('pending', 'approved', 'active', 'rejected') "
            ."NOT NULL DEFAULT 'approved'"
        );
    }
};

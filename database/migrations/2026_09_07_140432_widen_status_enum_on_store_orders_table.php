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
            'ALTER TABLE store_orders MODIFY COLUMN status '
            ."ENUM('draft', 'submitted', 'review', 'approved', 'rejected', 'issued') "
            ."NOT NULL DEFAULT 'draft'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "UPDATE store_orders SET status = 'submitted' WHERE status IN ('review', 'issued')"
        );

        DB::statement(
            'ALTER TABLE store_orders MODIFY COLUMN status '
            ."ENUM('draft', 'submitted', 'approved', 'rejected') "
            ."NOT NULL DEFAULT 'draft'"
        );
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Department plan items can now be added directly to a department plan.
     * Items distributed from an organizational plan keep their link.
     */
    public function up(): void
    {
        Schema::table('department_plan_items', function (Blueprint $table) {
            $table->uuid('organizational_plan_item_id')->nullable()->change();
        });
    }

    /**
     * Fails while items without an organizational plan item exist: they have no source to restore.
     */
    public function down(): void
    {
        Schema::table('department_plan_items', function (Blueprint $table) {
            $table->uuid('organizational_plan_item_id')->nullable(false)->change();
        });
    }
};

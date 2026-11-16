<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master records that are already referenced elsewhere are deactivated instead of deleted.
 * departments and designations already carry a status column.
 */
return new class extends Migration
{
    private array $tables = ['workstations', 'buildings', 'assetclasses', 'assets'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasColumn($name, 'status')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->enum('status', ['active', 'inactive'])->default('active');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (Schema::hasColumn($name, 'status')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};

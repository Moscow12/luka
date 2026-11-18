<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Some databases have a `frequency` column on chopitems that no migration created
 * and no code fills. It was NOT NULL without a default, so every item insert failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chopitems', 'frequency')) {
            return;
        }

        Schema::table('chopitems', function (Blueprint $table) {
            $table->string('frequency', 15)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Leave the column nullable: restoring NOT NULL would break inserts again
    }
};

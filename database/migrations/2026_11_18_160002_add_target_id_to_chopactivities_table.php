<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Links each planned intervention (activity) to the target it serves. Optional for existing rows. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chopactivities', function (Blueprint $table) {
            $table->foreignUuid('target_id')->nullable()->after('category_id')
                ->constrained('chop_targets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chopactivities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_id');
        });
    }
};

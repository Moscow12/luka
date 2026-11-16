<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the asset classes that engineering maintains, and which section looks after them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assetclasses', function (Blueprint $table) {
            $table->foreignUuid('eng_section_id')->nullable()->constrained('eng_sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assetclasses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eng_section_id');
        });
    }
};

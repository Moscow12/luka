<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['supervisor_title_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            // Modify the column to be nullable
            $table->uuid('supervisor_title_id')->nullable()->change();
        });

        Schema::table('departments', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('supervisor_title_id')
                ->references('id')
                ->on('jobtitles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['supervisor_title_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->uuid('supervisor_title_id')->nullable(false)->change();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('supervisor_title_id')
                ->references('id')
                ->on('jobtitles')
                ->onDelete('cascade');
        });
    }
};

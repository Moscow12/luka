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
        Schema::table('contract_requests', function (Blueprint $table) {
            // Employee attachment (termination letter, resignation letter, etc.)
            $table->string('employee_attachment')->nullable()->after('handover_notes');

            // HR confirmation letter (required for early termination)
            $table->string('hr_confirmation_letter')->nullable()->after('review_comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->dropColumn(['employee_attachment', 'hr_confirmation_letter']);
        });
    }
};

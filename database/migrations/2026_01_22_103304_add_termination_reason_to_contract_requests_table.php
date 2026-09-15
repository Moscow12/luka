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
            $table->foreignUuid('termination_reason_id')
                ->nullable()
                ->after('request_type')
                ->constrained('termination_reasons')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->dropForeign(['termination_reason_id']);
            $table->dropColumn('termination_reason_id');
        });
    }
};

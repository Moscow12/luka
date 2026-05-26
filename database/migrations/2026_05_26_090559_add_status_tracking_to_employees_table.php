<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignUuid('status_reason_id')
                ->nullable()
                ->after('status')
                ->constrained('termination_reasons')
                ->nullOnDelete();
            $table->date('status_changed_at')->nullable()->after('status_reason_id');
            $table->text('status_notes')->nullable()->after('status_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['status_reason_id']);
            $table->dropColumn(['status_reason_id', 'status_changed_at', 'status_notes']);
        });
    }
};

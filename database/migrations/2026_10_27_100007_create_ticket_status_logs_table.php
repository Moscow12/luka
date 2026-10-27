<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only history of every status change (source for ART and reopen-rate metrics)
        Schema::create('ticket_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignUuid('acted_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['to_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_status_logs');
    }
};

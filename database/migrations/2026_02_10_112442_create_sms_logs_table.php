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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sms_api_setting_id')->nullable()->constrained('sms_api_settings')->nullOnDelete();
            $table->string('phone_number');
            $table->text('message');
            $table->enum('status', ['sent', 'failed', 'pending', 'delivered'])->default('sent');
            $table->text('response')->nullable();
            $table->string('message_id')->nullable(); // API message ID for tracking
            $table->foreignUuid('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};

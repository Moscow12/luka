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
        Schema::create('sms_api_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider_name');
            $table->string('sender_id')->nullable();
            $table->text('sending_url');
            $table->text('delivery_report_url')->nullable();
            $table->text('sender_name_url')->nullable();
            $table->text('api_key');
            $table->text('secret_key');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_api_settings');
    }
};

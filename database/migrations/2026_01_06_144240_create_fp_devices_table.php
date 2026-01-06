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
        Schema::create('fp_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('ip_address');
            $table->integer('port')->default(4370);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'offline'])->default('inactive');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->integer('total_synced_logs')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fp_device_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fp_device_id')->constrained('fp_devices')->cascadeOnDelete();
            $table->string('user_id'); // ZKTeco user ID
            $table->timestamp('punch_time');
            $table->enum('punch_type', ['check_in', 'check_out', 'break_out', 'break_in', 'overtime_in', 'overtime_out'])->default('check_in');
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['fp_device_id', 'user_id', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fp_device_logs');
        Schema::dropIfExists('fp_devices');
    }
};

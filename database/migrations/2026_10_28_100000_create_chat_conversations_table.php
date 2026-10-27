<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'group', 'department']);
            $table->string('name')->nullable();
            $table->string('topic', 500)->nullable();
            $table->foreignUuid('department_id')->nullable()->unique()->constrained('departments')->cascadeOnDelete();
            // Sorted "userA:userB" so a DM between two people exists only once
            $table->string('direct_key', 80)->nullable()->unique();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};

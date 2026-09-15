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
        Schema::create('leaverequestapprovals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('leave_request_id')->constrained('employeeleaves')->cascadeOnDelete();
            $table->foreignUuid('approval_level_id')->constrained('approvallevels')->cascadeOnDelete();
            $table->foreignUuid('approver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['approved', 'rejected']);
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaverequestapprovals');
    }
};

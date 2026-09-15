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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->enum('type', ['supplier', 'service_provider', 'agency', 'partner']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'pending_approval'])->default('draft');
            $table->text('description')->nullable();
            $table->enum('notification_time', ['90', '60', '30'])->default('90');
            $table->foreignUuid('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

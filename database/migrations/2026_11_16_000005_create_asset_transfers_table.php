<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moving a register row (or part of a lot) to another department / location is requested and
 * approved here; the register row only changes when a transfer is approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assetregistry_id')->constrained('assetregistries')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->foreignUuid('from_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignUuid('from_facility_location_id')->constrained('facilitylocations')->restrictOnDelete();
            $table->foreignUuid('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignUuid('to_facility_location_id')->constrained('facilitylocations')->restrictOnDelete();
            $table->string('reason', 500);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignUuid('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            // Set when part of a lot moved: the new register row created for the moved items
            $table->foreignUuid('new_assetregistry_id')->nullable()->constrained('assetregistries')->nullOnDelete();
            $table->timestamps();

            $table->index(['assetregistry_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};

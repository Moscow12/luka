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
        Schema::create('assetregistries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_class_id')->constrained('assetclasses')->cascadeOnDelete();
            $table->foreignUuid('facility_location_id')->constrained('facilitylocations')->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignUuid('workstation_id')->constrained('workstations')->cascadeOnDelete();
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->string('serial_number')->unique()->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->string('vendor')->nullable();
            $table->foreignUuid('vendor_id')->constrained('vendors')->cascadeOnDelete()->nullable();
            $table->string('condition')->comment('e.g., new, good, fair, bad, poor, worse')->nullable();
            $table->decimal('depreciation', 15, 2)->default(0)->comment('Accumulated depreciation')->nullable();
            $table->decimal('depreciation_rate', 15, 2)->default(0)->comment('Depreciation rate as a percentage')->nullable();
            $table->integer('depreciation_period')->default(0)->comment('Depreciation period in months')->nullable();
            $table->string('model')->nullable();
            $table->string('make')->comment('wood, steel, aluminum, plastic, etc.')->nullable();
            $table->string('codeno')->comment('e.g., STJH/ICT/DESK/001, etc.')->nullable();
            $table->date('disposal_date')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assetregistries');
    }
};

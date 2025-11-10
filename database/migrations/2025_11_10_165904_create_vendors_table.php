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
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('vendor_type');
            $table->string('vendor_number')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('contact_person');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignUuid('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignUuid('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignUuid('ward_id')->nullable()->constrained('wards')->onDelete('cascade');
            $table->foreignUuid('vilstreet_id')->nullable()->constrained('streets')->onDelete('cascade');
            $table->foreignUuid('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};

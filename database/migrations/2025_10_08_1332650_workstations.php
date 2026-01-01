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
        Schema::create('workstations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('workstation_name', 250);            
            $table->string('location', 250)->default('Main Office')->nullable();
            $table->string('phone_number', 50);
            $table->string('tin_number', 50)->nullable();
            $table->string('email_address', 120)->nullable();
            $table->string('postal_code', 100)->nullable();
            $table->string('physical_address', 250)->nullable();
            $table->string('logo')->nullable();
            $table->string('official_stamp')->nullable();
            $table->string('letter_head')->nullable();
            $table->foreignUuid('country_id')->constrained('countries')->cascadeOnDelete();  
            $table->foreignUuid('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignUuid('district_id')->constrained('districts')->cascadeOnDelete();
            $table->foreignUuid('ward_id')->constrained('wards')->cascadeOnDelete();                      
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            //index on workstations table
            $table->index('workstation_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workstations');
    }
};

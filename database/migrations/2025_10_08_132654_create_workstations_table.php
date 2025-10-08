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
            $table->string('Workstation_name', 250);
            $table->string('StationLocation', 250);
            $table->string('StationPhone_Number', 50);
            $table->string('Tin_Number', 50);
            $table->string('StationEmail_Address', 120);
            $table->string('StationAddress', 250);
            $table->string('StationCity', 100);
            $table->string('StationProvince', 100);
            $table->string('StationCountry', 100);
            $table->string('StationPostalCode', 100);
            $table->foreignUuid('Added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
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

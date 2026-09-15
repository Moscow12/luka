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
        Schema::create('employeeattendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('fpuser_id');
            $table->date('clockdate')->nullable();
            $table->string('device_id')->nullable();
            $table->string('clocktimestamp');
            $table->string('status')->nullable();
            $table->time('clocktime')->nullable();
            $table->string('clock_status')->nullable();
            $table->string('clock_in')->nullable();
            $table->string('clock_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeeattendances');
    }
};

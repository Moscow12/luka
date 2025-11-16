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
        Schema::dropIfExists('source_of_funds');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot recreate the table as we're removing it permanently
    }
};

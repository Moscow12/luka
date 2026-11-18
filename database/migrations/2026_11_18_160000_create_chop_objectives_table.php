<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CHOP objectives, e.g. "Improve access, quality and equitable social services delivery".
 * Long-lived: reused across financial years. Targets hang off them per year.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chop_objectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->nullable();
            $table->string('name', 500);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chop_objectives');
    }
};

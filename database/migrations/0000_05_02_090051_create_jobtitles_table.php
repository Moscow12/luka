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
        Schema::create('jobtitles', function (Blueprint $table) {
            // Primary key as UUID
            $table->uuid('id')->primary();

            // Name of the job title, indexed for efficient lookups
            $table->string('name')->index();

            // Code for the job title, unique
            $table->string('code')->unique();
 
            // Description of the job title
            $table->text('description');

            // User who created the job title
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();

            // Soft delete column for marking records as deleted
            $table->softDeletes();

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobtitles');
    }
};

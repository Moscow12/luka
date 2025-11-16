<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the correct foreign key
        // Since SQLite doesn't support dropping foreign keys

        // Enable foreign key constraints
        DB::statement('PRAGMA foreign_keys = OFF');

        // Create temporary table with correct structure
        Schema::create('chopitems_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->string('gfc_code')->nullable();
            $table->string('unit')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('chopcategoryareas')->cascadeOnDelete();
            $table->timestamps();
        });

        // Copy data from old table to new table
        DB::statement('INSERT INTO chopitems_temp SELECT * FROM chopitems');

        // Drop old table
        Schema::dropIfExists('chopitems');

        // Rename temp table to original name
        Schema::rename('chopitems_temp', 'chopitems');

        // Re-enable foreign key constraints
        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migration not needed as this is a fix
    }
};

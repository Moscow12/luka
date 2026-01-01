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
        $driver = Schema::getConnection()->getDriverName();

        // Disable foreign key checks based on database driver
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

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

        // Re-enable foreign key checks based on database driver
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migration not needed as this is a fix
    }
};

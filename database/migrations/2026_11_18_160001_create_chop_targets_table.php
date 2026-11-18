<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time-bound CHOP targets under an objective, e.g. "Shortage of medicines ... reduced
 * from 5% to 0% by June 2026". Each belongs to one financial year.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chop_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // An objective with targets cannot be deleted
            $table->foreignUuid('objective_id')->constrained('chop_objectives')->restrictOnDelete();
            $table->foreignUuid('financial_year_id')->constrained('financial_years')->cascadeOnDelete();
            $table->string('code', 20)->nullable();
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['financial_year_id', 'objective_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chop_targets');
    }
};

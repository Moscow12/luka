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
        Schema::create('approval_mapping_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('mapping_id')
                ->constrained('approvalleveltoemployees', 'id', 'amd_mapping_id_foreign')
                ->cascadeOnDelete();
            $table->foreignUuid('department_id')
                ->constrained('departments', 'id', 'amd_department_id_foreign')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mapping_id', 'department_id'], 'amd_mapping_department_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_mapping_departments');
    }
};

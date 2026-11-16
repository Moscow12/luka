<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Facility locations become the single room register (replacing the engineering-only rooms table):
 * each room belongs to a department, so picking a department lists its rooms.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilitylocations', function (Blueprint $table) {
            $table->foreignUuid('department_id')->nullable()->after('workstation_id')->constrained('departments')->restrictOnDelete();
            $table->string('code', 30)->nullable()->after('name');
            $table->string('floor', 60)->nullable()->after('code');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('department_id');

            $table->unique(['department_id', 'code']);
        });

        // Backfill: a location whose registered assets all belong to one department gets that department.
        // Mixed or empty locations stay null and are listed as "No department" for manual cleanup.
        DB::table('assetregistries')
            ->whereNull('deleted_at')
            ->select('facility_location_id')
            ->selectRaw('MIN(department_id) as department_id')
            ->groupBy('facility_location_id')
            ->havingRaw('COUNT(DISTINCT department_id) = 1')
            ->get()
            ->each(fn ($row) => DB::table('facilitylocations')
                ->where('id', $row->facility_location_id)
                ->whereNull('department_id')
                ->update(['department_id' => $row->department_id]));
    }

    public function down(): void
    {
        Schema::table('facilitylocations', function (Blueprint $table) {
            $table->dropUnique(['department_id', 'code']);
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['code', 'floor', 'status']);
        });
    }
};

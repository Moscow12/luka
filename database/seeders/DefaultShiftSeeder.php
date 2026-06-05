<?php

namespace Database\Seeders;

use App\Models\shifts;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultShiftSeeder extends Seeder
{
    /**
     * Seed a single, correctly-formatted default shift used for attendance
     * late-detection when an employee has no roster in the viewed month.
     *
     * count_late is stored as a number of grace minutes (integer), matching
     * the Setup\Shiftmngts form and the attendance late-detection logic.
     */
    public function run(): void
    {
        $addedBy = User::query()->value('id') ?? User::factory()->create()->id;

        DB::transaction(function () use ($addedBy) {
            $shift = shifts::updateOrCreate(
                ['name' => 'Default Shift'],
                [
                    'description' => 'Default shift applied when an employee has no roster.',
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'status' => 'active',
                    'is_default' => true,
                    'count_early' => 15,
                    'count_late' => 15,
                    'added_by' => $addedBy,
                ]
            );

            // Only one shift may be the default at a time.
            shifts::where('id', '!=', $shift->id)->update(['is_default' => false]);
        });

        $this->command?->info('Default shift seeded: 08:00–17:00, 15 min grace.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            ['name' => 'Morning', 'start_time' => '08:00', 'end_time' => '12:00', 'status' => 'active', 'count_early' => '00:30', 'count_late' => '00:30', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Afternoon', 'start_time' => '13:00', 'end_time' => '17:00', 'status' => 'active', 'count_early' => '00:30', 'count_late' => '00:30', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Evening', 'start_time' => '18:00', 'end_time' => '23:00', 'status' => 'active', 'count_early' => '00:30', 'count_late' => '00:30', 'added_by' => \App\Models\User::factory()->create()->id],
        ];

        foreach ($shifts as $shift) {
            \App\Models\shifts::updateOrCreate(
                ['name' => $shift['name'], 'start_time' => $shift['start_time'], 'end_time' => $shift['end_time'], 'status' => $shift['status'], 'count_early' => $shift['count_early'], 'count_late' => $shift['count_late'], 'added_by' => $shift['added_by']],
                $shift
            );
        }
    }
}

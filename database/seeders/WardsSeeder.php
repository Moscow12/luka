<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add ward for dar es salaam
        $wards = [
            ['name' => 'Kigamboni', 'district_id' => \App\Models\districts::where('name', 'Ilala')->first()?->id ?? null],
            ['name' => 'Mbagala', 'district_id' => \App\Models\districts::where('name', 'Ilala')->first()?->id ?? null],
            // add more wards as needed
        ];

        foreach ($wards as $ward) {
            \App\Models\wards::updateOrCreate(
                ['name' => $ward['name'], 'district_id' => $ward['district_id']],
                $ward
            );
        }
    }
}

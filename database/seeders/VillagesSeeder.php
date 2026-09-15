<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VillagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add villages for dar es salaam
        $villages = [
            ['name' => 'Kigamboni Mwisho', 'ward_id' => \App\Models\wards::where('name', 'Kigamboni')->first()?->id ?? null],
            ['name' => 'Mbagala Mtoni', 'ward_id' => \App\Models\wards::where('name', 'Mbagala')->first()?->id ?? null],
            // add more villages as needed
        ];

        foreach ($villages as $village) {
            \App\Models\villages::updateOrCreate(
                ['name' => $village['name'], 'ward_id' => $village['ward_id']],
                $village
            );
        }
    }
}

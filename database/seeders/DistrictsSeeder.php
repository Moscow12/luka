<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your districts data here
        $districts = [
            ['name' => 'Ilala', 'region_id' => \App\Models\regions::where('name', 'Dar es Salaam')->first()?->id ?? null],
            ['name' => 'Kinondoni', 'region_id' => \App\Models\regions::where('name', 'Dar es Salaam')->first()?->id ?? null],
            ['name' => 'Temeke', 'region_id' => \App\Models\regions::where('name', 'Dar es Salaam')->first()?->id ?? null],
            ['name' => 'Arusha District', 'region_id' => \App\Models\regions::where('name', 'Arusha')->first()?->id ?? null],
            ['name' => 'Moshi District', 'region_id' => \App\Models\regions::where('name', 'Kilimanjaro')->first()?->id ?? null],
            ['name' => 'Dodoma District', 'region_id' => \App\Models\regions::where('name', 'Dodoma')->first()?->id ?? null],
            ['name' => 'Mwanza District', 'region_id' => \App\Models\regions::where('name', 'Mwanza')->first()?->id ?? null],
            ['name' => 'Mbeya District', 'region_id' => \App\Models\regions::where('name', 'Mbeya')->first()?->id ?? null],
            ['name' => 'Morogoro District', 'region_id' => \App\Models\regions::where('name', 'Morogoro')->first()?->id ?? null],
            ['name' => 'Tanga District', 'region_id' => \App\Models\regions::where('name', 'Tanga')->first()?->id ?? null],
            // Add more districts as needed
        ];

        foreach ($districts as $district) {
            \App\Models\districts::updateOrCreate(
                ['name' => $district['name'], 'region_id' => $district['region_id']],
                $district
            );
        }
    }
}

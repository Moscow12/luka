<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your regions data here
        $regions = [
            ['name' => 'Arusha'],
            ['name' => 'Dar es Salaam'],
            ['name' => 'Dodoma'],
            ['name' => 'Kilimanjaro'],
            ['name' => 'Mwanza'],
            ['name' => 'Mbeya'],
            ['name' => 'Morogoro'],
            ['name' => 'Tanga'],
            ['name' => 'Zanzibar'],
            // Add more regions as needed
        ];

        foreach ($regions as $region) {
            \App\Models\regions::updateOrCreate(
                ['name' => $region['name'], 'country_id' => \App\Models\countries::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339'],
                $region
            );
        }
    }
}

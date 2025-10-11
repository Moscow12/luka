<?php

namespace Database\Seeders;

use App\Models\countries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your countries data here
        countries::create(['name' => 'Tanzania', 'code' => 'TZ', 'shortcode' => '+255']);
    }
}

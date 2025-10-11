<?php

namespace Database\Seeders;

use App\Models\countries as ModelsCountries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Countries extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ModelsCountries::create(['name' => 'Tanzania', 'code' => 'TZ', 'shortcode' => '+255']);
    }
}

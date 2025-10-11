<?php

namespace Database\Seeders;

use App\Models\religions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReligionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        religions::create(
            ['name' => 'Christianity'], 
            ['name' => 'Islam'], ['name' => 'Hinduism'], ['name' => 'Paganism'], ['name' => 'Buddhism'], 
            ['name' => 'Judaism'], ['name' => 'Atheism'], ['name' => 'Other']);
    }
}

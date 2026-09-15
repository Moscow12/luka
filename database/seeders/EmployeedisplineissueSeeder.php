<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeedisplineissueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your employeedisplineissue data here
        \App\Models\Employeedisplineissue::factory()->count(1)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Employeedeductions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeedeductionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employeedeductions::factory()->count(1)->create();
    }
}

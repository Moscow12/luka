<?php

namespace Database\Seeders;

use App\Models\Employeeallowances;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeallowancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employeeallowances::factory()->count(1)->create();
    }
}

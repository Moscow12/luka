<?php

namespace Database\Seeders;

use App\Models\Employeesalaries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeesalariesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Employeesalaries::factory()->create(); 
    }
}

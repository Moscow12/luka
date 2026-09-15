<?php

namespace Database\Seeders;

use App\Models\Employeequalifications;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeequalificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your employeequalifications data here
        Employeequalifications::factory()->count(1)->create();
    }
}

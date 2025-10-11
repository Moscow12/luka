<?php

namespace Database\Seeders;

use App\Models\departments;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        departments::create(
            ['name' => 'Human Resources', 'description'=>'HR', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Information Technology', 'description'=>'IT', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Administration', 'description'=>'Administration', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Medical Ward', 'description'=>'MW', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Surgical Ward', 'description'=>'SW', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Dental Ward', 'description'=>'DW', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Pharmacy', 'description'=>'Pharmacy', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Laboratory', 'description'=>'Laboratory', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'OPD', 'description'=>'OPD', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Accounting', 'description'=>'Accounting', 'added_by' => \App\Models\User::factory()->create()->id],
        );
    }
}

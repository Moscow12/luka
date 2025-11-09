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
            ['name' => 'Human Resources', 'description'=>'HR','supervisor_title_id'=>\App\Models\Jobtitle::first()->id, 'added_by' => \App\Models\User::factory()->create()->id],

            ['name' => 'Finance', 'description'=>'Finance', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Accounting', 'description'=>'Accounting', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'IT', 'description'=>'IT', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Information Technology', 'description'=>'IT', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Administration', 'description'=>'Administration', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Medical Ward', 'description'=>'MW', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Surgical Ward', 'description'=>'SW', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Dental Ward', 'description'=>'DW', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Pharmacy', 'description'=>'Pharmacy', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Laboratory', 'description'=>'Laboratory', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'OPD', 'description'=>'OPD', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
            ['name' => 'Accounting', 'description'=>'Accounting', 'added_by' => \App\Models\User::factory()->create()->id, 'supervisor_title_id'=>\App\Models\Jobtitle::first()->id],
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\job_titles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Job_titlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        job_titles::create(
            ['title' => 'Medical Doctor', 'code' => 'MD', 'description'=>'MD', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Registered Nurse', 'code' => 'RN', 'description'=>'RN', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Laboratory Technician', 'code' => 'LT', 'description'=>'LT', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Radiologic Technologist', 'code' => 'RT', 'description'=>'RT', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Pharmacist', 'code' => 'Pharm', 'description'=>'Pharm', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Medical Administrator', 'code' => 'MA', 'description'=>'MA', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Head Of Department', 'code' => 'HOD', 'description'=>'HOD', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Specialist Consultant', 'code' => 'MMed', 'description'=>'MMed', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Medical Assistant', 'code' => 'MA', 'description'=>'MA', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Healthcare Assistant', 'code' => 'HA', 'description'=>'HA', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Medical Technician', 'code' => 'MTech', 'description'=>'MTech', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Dental Technician', 'code' => 'DTech', 'description'=>'DTech', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Pharmacy Technician', 'code' => 'PTech', 'description'=>'PTech', 'added_by' => \App\Models\User::factory()],
            ['title' => 'Nursing Assistant', 'code' => 'NA', 'description'=>'NA', 'added_by' => \App\Models\User::factory()],   
            
        );
    }
}

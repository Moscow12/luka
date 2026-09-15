<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeavesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add your leaves data here
        $leaves = [
            ['name' => 'Annual Leave', 'description'=>'Annual Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Sick Leave', 'description'=>'Sick Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Holiday Leave', 'description'=>'Holiday Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Maternity Leave', 'description'=>'Maternity Leave', 'days'=>84, 'gender'=>'Female', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Paternity Leave', 'description'=>'Paternity Leave', 'days'=>4, 'gender'=>'Male', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Parental Leave', 'description'=>'Parental Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Spousal Leave', 'description'=>'Spousal Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Widow Leave', 'description'=>'Widow Leave', 'days'=>15, 'gender'=>'Both', 'status'=>'active', 'added_by' => \App\Models\User::factory()->create()->id],
        ];

        foreach ($leaves as $leave) {
            \App\Models\Leaves::updateOrCreate(
                ['name' => $leave['name'], 'description' => $leave['description'], 'days' => $leave['days'], 'gender' => $leave['gender'], 'status' => $leave['status'], 'added_by' => $leave['added_by']],
                $leave
            );
        }
    }
}

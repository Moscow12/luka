<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViolationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violations = [
            ['violation_type' => 'Leave Violation', 'description' => 'Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Vacation Violation', 'description' => 'Vacation Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Sick Leave Violation', 'description' => 'Sick Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Holiday Violation', 'description' => 'Holiday Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Maternity Leave Violation', 'description' => 'Maternity Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Paternity Leave Violation', 'description' => 'Paternity Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Parental Leave Violation', 'description' => 'Parental Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Spousal Leave Violation', 'description' => 'Spousal Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
            ['violation_type' => 'Widow Leave Violation', 'description' => 'Widow Leave Violation', 'added_by' => \App\Models\User::factory()->create()->id],
        ];

        foreach ($violations as $violation) {
            \App\Models\violations::updateOrCreate(
                ['violation_type' => $violation['violation_type'], 'description' => $violation['description'], 'added_by' => $violation['added_by']],
                $violation
            );
        }   
    }
}

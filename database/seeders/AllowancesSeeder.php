<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AllowancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //'Pay_Grade',
        // 'Job_Title',
        // 'Minimum_Salary',
        // 'Mid_Point_Salary',
        // 'Maximum_Salary',
        // 'Description',

        $allowances = [
            ['Pay_Grade' => 'Mid-range', 'Job_Title' => 'Registered Nurse', 'Minimum_Salary' => 10000, 'Mid_Point_Salary' => 20000, 'Maximum_Salary' => 30000, 'Description' => 'Mid-range', 'added_by' => \App\Models\User::factory()->create()->id],
            ['Pay_Grade' => 'High-range', 'Job_Title' => 'Registered Nurse', 'Minimum_Salary' => 30000, 'Mid_Point_Salary' => 40000, 'Maximum_Salary' => 50000, 'Description' => 'High-range', 'added_by' => \App\Models\User::factory()->create()->id],
            ['Pay_Grade' => 'Low-range', 'Job_Title' => 'Registered Nurse', 'Minimum_Salary' => 50000, 'Mid_Point_Salary' => 60000, 'Maximum_Salary' => 70000, 'Description' => 'Low-range', 'added_by' => \App\Models\User::factory()->create()->id],
            ['Pay_Grade' => 'Lowest-range', 'Job_Title' => 'Registered Nurse', 'Minimum_Salary' => 70000, 'Mid_Point_Salary' => 80000, 'Maximum_Salary' => 90000, 'Description' => 'Lowest-range', 'added_by' => \App\Models\User::factory()->create()->id],
        ];

        foreach ($allowances as $allowance) {
            \App\Models\allowances::updateOrCreate(
                ['Pay_Grade' => $allowance['Pay_Grade'], 'Job_Title' => $allowance['Job_Title'], 'Minimum_Salary' => $allowance['Minimum_Salary'], 'Mid_Point_Salary' => $allowance['Mid_Point_Salary'], 'Maximum_Salary' => $allowance['Maximum_Salary'], 'Description' => $allowance['Description'], 'added_by' => $allowance['added_by']],
                $allowance
            );
        }
    }
}

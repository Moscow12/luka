<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialYearsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financialYears = [
            [
                'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_current' => true, 'added_by' => \App\Models\User::factory()->create()->id],
            ];

        foreach ($financialYears as $financialYear) {
            \App\Models\financial_years::updateOrCreate(
                ['start_date' => $financialYear['start_date'], 'end_date' => $financialYear['end_date'], 'is_current' => $financialYear['is_current'], 'added_by' => $financialYear['added_by']],
                $financialYear
            );
        }
    }
}

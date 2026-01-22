<?php

namespace Database\Seeders;

use App\Models\Deduction;
use Illuminate\Database\Seeder;

class DeductionSeeder extends Seeder
{
    public function run(): void
    {
        $deductions = [
            
            [
                'name' => 'NSSF Contribution',
                'type' => 'percentage',
                'deduction_value' => 10,
                'applies_to' => 'gross',
                'deduction_type' => 'mafao',
                'description' => 'National Social Security Fund contribution (10%)',
            ],
            [
                'name' => 'NHIF Contribution',
                'type' => 'percentage',
                'deduction_value' => 3,
                'applies_to' => 'gross',
                'deduction_type' => 'non',
                'description' => 'National Health Insurance Fund deduction',
            ],
            [
                'name' => 'WCF',
                'type' => 'percentage',
                'deduction_value' => 3,
                'applies_to' => 'net',
                'deduction_type' => 'non',
                'description' => 'Monthly WCF',
            ],
        ];

        foreach ($deductions as $data) {
            Deduction::updateOrCreate(
                ['name' => $data['name']],
                $data
                // select user id for added_by
                + ['added_by' => \App\Models\User::factory()->create()->id],
            );
        }
    }
}

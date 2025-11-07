<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deductions;

class DeductionSeeder extends Seeder
{
    public function run(): void
    {
        $deductions = [
            [
                'name' => 'PAYE',
                'type' => 'percentage',
                'deduction_value' => 10,
                'applies_to' => 'gross',
                'description' => 'Pay As You Earn tax deduction (10%)',
            ],
            [
                'name' => 'NSSF Contribution',
                'type' => 'percentage',
                'deduction_value' => 10,
                'applies_to' => 'gross',
                'description' => 'National Social Security Fund contribution (10%)',
            ],
            [
                'name' => 'NHIF Contribution',
                'type' => 'fixed',
                'deduction_value' => 10000,
                'applies_to' => 'gross',
                'description' => 'National Health Insurance Fund deduction',
            ],
            [
                'name' => 'Loan Repayment',
                'type' => 'fixed',
                'deduction_value' => 50000,
                'applies_to' => 'net',
                'description' => 'Monthly loan repayment for staff loans',
            ],
        ];

        foreach ($deductions as $data) {
            Deductions::updateOrCreate(
                ['name' => $data['name']],
                $data
                // select user id for added_by
                + ['added_by' => \App\Models\User::factory()->create()->id],
            );
        }
    }
}

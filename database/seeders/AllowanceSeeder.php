<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\allowances;

class AllowanceSeeder extends Seeder
{
    public function run(): void
    {
        $allowances = [
            [
                'name' => 'Housing Allowance',
                'type' => 'fixed',
                'allowance_value' => 200000,
                'taxable' => true,
                'description' => 'Monthly housing benefit for staff',
            ],
            [
                'name' => 'Transport Allowance',
                'type' => 'fixed',
                'allowance_value' => 80000,
                'taxable' => false,
                'description' => 'Monthly transportation allowance',
            ],
            [
                'name' => 'Responsibility Allowance',
                'type' => 'percentage',
                'allowance_value' => 10,
                'taxable' => true,
                'description' => 'Calculated as 10% of base salary for department heads',
            ],
            [
                'name' => 'Medical Allowance',
                'type' => 'fixed',
                'allowance_value' => 50000,
                'taxable' => false,
                'description' => 'Health support for employees',
            ],
        ];

        foreach ($allowances as $data) {
            allowances::updateOrCreate(
                ['name' => $data['name']],
                $data
                // select user id for added_by
                + ['added_by' => \App\Models\User::factory()->create()->id],
            );
        }
    }
}

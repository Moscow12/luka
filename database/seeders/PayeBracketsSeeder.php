<?php

namespace Database\Seeders;

use App\Models\paye_brackets;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayeBracketsSeeder extends Seeder
{
    /**
     * Seed Tanzania Mainland PAYE tax brackets
     * Based on Tanzania Revenue Authority (TRA) rates
     * These are MONTHLY income tax brackets
     */
    public function run(): void
    {
        // Get the first admin user or create one for seeding
        $user = User::first();

        if (!$user) {
            $this->command->warn('No user found. Please create a user first.');
            return;
        }

        $brackets = [
            [
                'min_amount' => 0,
                'max_amount' => 270000,
                'rate' => 0,
                'fixed_amount' => 0,
                'description' => 'First TZS 270,000 (Tax Free)',
                'order' => 1,
                'is_active' => true,
                'added_by' => $user->id,
            ],
            [
                'min_amount' => 270000,
                'max_amount' => 520000,
                'rate' => 8,
                'fixed_amount' => 0,
                'description' => 'TZS 270,001 to TZS 520,000 (8%)',
                'order' => 2,
                'is_active' => true,
                'added_by' => $user->id,
            ],
            [
                'min_amount' => 520000,
                'max_amount' => 760000,
                'rate' => 20,
                'fixed_amount' => 0,
                'description' => 'TZS 520,001 to TZS 760,000 (20%)',
                'order' => 3,
                'is_active' => true,
                'added_by' => $user->id,
            ],
            [
                'min_amount' => 760000,
                'max_amount' => 1000000,
                'rate' => 25,
                'fixed_amount' => 0,
                'description' => 'TZS 760,001 to TZS 1,000,000 (25%)',
                'order' => 4,
                'is_active' => true,
                'added_by' => $user->id,
            ],
            [
                'min_amount' => 1000000,
                'max_amount' => null,
                'rate' => 30,
                'fixed_amount' => 0,
                'description' => 'Above TZS 1,000,000 (30%)',
                'order' => 5,
                'is_active' => true,
                'added_by' => $user->id,
            ],
        ];

        foreach ($brackets as $data) {
            paye_brackets::updateOrCreate(
                [
                    'min_amount' => $data['min_amount'],
                    'rate' => $data['rate'],
                ],
                $data
            );
        }

        $this->command->info('Tanzania Mainland PAYE brackets seeded successfully!');
    }
}

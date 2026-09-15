<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deductions>
 */
class DeductionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['PAYE', 'Starehe Na Maafa', 'NHIF','NSSF']),
            'modepercentage' => $this->faker->randomElement(['true', 'false']),
            'Deduction_Type' => $this->faker->randomElement(['Mafao', 'Other', 'TAX','Non']),
            'Amount' => $this->faker->randomFloat(1000, 0, 10000000),
            'Description' => $this->faker->sentence(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractDeduction>
 */
class ContractDeductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => \App\Models\Employeecontracts::inRandomOrder()->first()?->id ?? \App\Models\Employeecontracts::factory(),
            'deduction_id' => \App\Models\Deduction::inRandomOrder()->first()?->id ?? \App\Models\Deduction::factory(),
            'amount_override' => $this->faker->optional(0.7)->randomFloat(2, 10000, 200000),
            'is_active' => $this->faker->boolean(85), // 85% chance active
            'description' => $this->faker->optional()->sentence(10),
            'added_by' => \App\Models\User::inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
        ];
    }

    /**
     * State for active deductions
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * State for inactive deductions
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractAllowance>
 */
class ContractAllowanceFactory extends Factory
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
            'allowance_id' => \App\Models\allowances::inRandomOrder()->first()?->id ?? \App\Models\allowances::factory(),
            'amount_override' => $this->faker->optional(0.7)->randomFloat(2, 50000, 500000),
            'is_active' => $this->faker->boolean(85), // 85% chance active
            'description' => $this->faker->optional()->sentence(10),
            'added_by' => \App\Models\User::inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
        ];
    }

    /**
     * State for active allowances
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
     * State for inactive allowances
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

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeeallowances>
 */
class EmployeeallowancesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'allowance_id' => \App\Models\allowances::factory(),
            'allowance_amount' => $this->faker->randomFloat(1000, 0, 10000000),
            'salary_id' => \App\Models\Employeesalaries::factory(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeedeductions>
 */
class EmployeedeductionsFactory extends Factory
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
            'deductions_id' => \App\Models\Deductions::factory(),
            'deductions_amount' => $this->faker->randomFloat(1000, 0, 10000000),
            'salary_id' => \App\Models\Employeesalaries::factory(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

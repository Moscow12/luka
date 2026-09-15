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
            'employee_id' => \App\Models\Employee::inRandomOrder()->first()?->id ?? \App\Models\Employee::factory(),
            'deductions_id' => \App\Models\Deduction::inRandomOrder()->first()?->id ?? \App\Models\Deduction::factory(),
            'deductions_amount' => $this->faker->randomFloat(2, 10000, 200000),
            'salary_id' => \App\Models\Employeesalaries::inRandomOrder()->first()?->id ?? \App\Models\Employeesalaries::factory(),
        ];
    }
}

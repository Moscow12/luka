<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeesalaries>
 */
class EmployeesalariesFactory extends Factory
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
            'contract_id' => \App\Models\Employeecontracts::factory(),
            'amount' => $this->faker->randomFloat(1000, 0, 10000000),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

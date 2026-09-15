<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeepromotions>
 */
class EmployeepromotionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // // title_id, workstation_id, department_id, start_date, attachment, comments, employee_id, added_by
            'title_id' => \App\Models\Jobtitle::factory(),
            'workstation_id' => \App\Models\Workstations::factory(),
            'department_id' => \App\Models\departments::factory(),
            'start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'attachment' => $this->faker->sentence(),
            'comments' => $this->faker->sentence(),
            'employee_id' => \App\Models\Employee::factory(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

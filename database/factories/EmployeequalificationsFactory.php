<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeequalifications>
 */
class EmployeequalificationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //Eductation_level, Institution, start_date, end_date, attachment, comments, employee_id, added_by
        return [
            'Eductation_level' => $this->faker->randomElement(['Primary', 'Diploma', 'Certificate', 'Degree', 'Masters', 'PhD']),
            'Institution' => $this->faker->company(),
            'start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'end_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'attachment' => $this->faker->sentence(),
            'comments' => $this->faker->sentence(),
            'employee_id' => \App\Models\Employee::factory(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeedisplineissue>
 */
class EmployeedisplineissueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            ////violation_id, employee_id, violation_date, notes, attachment, added_by
            'violation_id' => \App\Models\Violations::factory(),
            'employee_id' => \App\Models\Employee::factory(),
            'violation_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'notes' => $this->faker->sentence(),
            'attachment' => $this->faker->sentence(),   
        ];
    }
}

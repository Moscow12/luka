<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeeleaves>
 */
class EmployeeleavesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //leave_id, employee_id, start_date, end_date, days, travel_to, othercontact, comments, added_by
            'leave_id' => \App\Models\Leaves::factory(),
            'employee_id' => \App\Models\Employee::factory(),
            'start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'end_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'days' => $this->faker->randomElement(['Annual Leave', 'Sick Leave', 'Holiday Leave', 'Maternity Leave', 'Paternity Leave', 'Parental Leave', 'Spousal Leave', 'Widow Leave']),
            'travel_to' => $this->faker->randomElement(['Yes', 'No']),
            'othercontact' => $this->faker->randomElement(['Yes', 'No']),
            'comments' => $this->faker->sentence(),
        ];
    }
}

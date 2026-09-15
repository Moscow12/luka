<?php

namespace Database\Factories;

use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\Jobtitle;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        $hireDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $birthDate = $this->faker->dateTimeBetween('-60 years', '-22 years');

        return [
            'user_id' => null, // Assuming user_id will be set later
            'employee_no' => 'EMP'.$this->faker->unique()->numberBetween(1000, 9999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'dob' => $birthDate,
            'national_id' => $this->faker->unique()->numerify('###############'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'employment_type' => $this->faker->randomElement(['Full-time', 'Part-time']),
            'hired_date' => $hireDate,
            'department_id' => departments::inRandomOrder()->first()?->id ?? departments::factory(),
            'education_level' => $this->faker->randomElement(['Primary', 'Diploma', 'Certificate', 'Degree', 'Masters', 'PhD']),
            'fpid' => $this->faker->unique()->numerify('########'),
            'photo' => null, // Assuming photo will be set later
            'marital_status' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed', 'Separated', 'Never married', 'Not applicable']),
            'status' => 'Active',
            'title_id' => Jobtitle::inRandomOrder()->first()?->id ?? Jobtitle::factory(),
            'country_id' => \App\Models\countries::inRandomOrder()->first()?->id,
            'region_id' => \App\Models\regions::inRandomOrder()->first()?->id,
            'district_id' => \App\Models\districts::inRandomOrder()->first()?->id,
            'ward_id' => \App\Models\wards::inRandomOrder()->first()?->id,
            'vilstreet_id' => \App\Models\street::inRandomOrder()->first()?->id,

            'tin_number' => null, // Assuming tin_number will be set later
            'designation_id' => designations::inRandomOrder()->first()?->id ?? designations::factory(),
            'workstation_id' => \App\Models\workstations::inRandomOrder()->first()?->id,
            'denomination_id' => \App\Models\denominations::inRandomOrder()->first()?->id,
            'added_by' => \App\Models\User::inRandomOrder()->first()?->id,

        ];
    }

    // States for different employee scenarios
    public function probation()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'probation',
                'hired_date' => now()->subMonths(3),
            ];
        });
    }

    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
            ];
        });
    }

    public function terminated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'terminated',
            ];
        });
    }
}

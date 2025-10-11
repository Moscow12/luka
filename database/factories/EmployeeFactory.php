<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\departments;
use App\Models\designations;
use App\Models\job_titles;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        $hireDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $birthDate = $this->faker->dateTimeBetween('-60 years', '-22 years');

        return [
            'uuid'=>(string) \Illuminate\Support\Str::uuid(),
            'user_id'=>null, // Assuming user_id will be set later
            'employee_no'=>'EMP'.$this->faker->unique()->numberBetween(1000, 9999),
            'first_name'=>$this->faker->firstName(),
            'last_name'=>$this->faker->lastName(),
            'gender'=>$this->faker->randomElement(['male', 'female']),
            'dob'=>$birthDate,
            'national_id'=>$this->faker->unique()->numerify('###############'),
            'phone' =>$this->faker->phoneNumber(),
            'email'=>$this->faker->unique()->safeEmail(),
            'employment_type'=>$this->faker->randomElement(['full_time', 'part_time']),
            'hired_date'=>$hireDate,
            'department_id'=>departments::factory(),
            'education_level'=>$this->faker->randomElement(['primary', 'diploma', 'certificate', 'degree', 'masters', 'phd']),
            'fpid'=>$this->faker->unique()->numerify('########'),
            'photo'=>null, // Assuming photo will be set later
            'marital_status'=>$this->faker->randomElement(['single', 'married', 'divorced']),
            'status'=>'active',
            'job_title_id'=>job_titles::factory(),
            'country_id'=>\App\Models\countries::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'region_id'=>\App\Models\regions::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'district_id'=>\App\Models\districts::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'ward_id'=>\App\Models\wards::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'vilstreet_id'=>\App\Models\villages::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            
            'tin_number'=>null, // Assuming tin_number will be set later
            'designation_id'=>designations::factory(),
            'workstation_id'=>\App\Models\workstations::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'denomination_id'=>\App\Models\denominations::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            'added_by'=>\App\Models\User::first()?->id ?? '0199cee7-b86b-7145-8710-5715fcdc8339',
            
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
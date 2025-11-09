<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employeecontracts>
 */
class EmployeecontractsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $expireDate = $this->faker->dateTimeBetween($startDate, '+3 years');

        return [
            'employee_id' => \App\Models\Employee::inRandomOrder()->first()?->id ?? \App\Models\Employee::factory(),
            'workstation_id' => \App\Models\workstations::inRandomOrder()->first()?->id ?? \App\Models\workstations::factory(),
            'department_id' => \App\Models\departments::inRandomOrder()->first()?->id,
            'position_id' => \App\Models\Jobtitle::inRandomOrder()->first()?->id ?? \App\Models\Jobtitle::factory(),
            'contract_type' => $this->faker->randomElement(['permanent', 'temporary', 'part_time']),
            'status' => $this->faker->randomElement(['active', 'expired', 'suspended', 'terminated']),
            'start_date' => $startDate,
            'expire_date' => $expireDate,
            'expirenotification' => $this->faker->boolean(70), // 70% chance true
            'notify_time' => $this->faker->randomElement(['7 days', '14 days', '30 days', '60 days', '90 days']),
            'payment_frequency' => $this->faker->randomElement(['monthly', 'weekly', 'bi-weekly', 'daily', 'hourly']),
            'base_salary' => $this->faker->randomFloat(2, 300000, 5000000),
            'attachment' => 'contracts/contract_'.$this->faker->uuid().'.pdf',
            'description' => $this->faker->sentence(10),
            'added_by' => \App\Models\User::inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
        ];
    }

    /**
     * State for active contracts
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'expire_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            ];
        });
    }

    /**
     * State for temporary contracts
     */
    public function temporary()
    {
        return $this->state(function (array $attributes) {
            return [
                'contract_type' => 'temporary',
                'status' => 'active',
                'start_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'expire_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            ];
        });
    }

    /**
     * State for expired contracts
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'start_date' => $this->faker->dateTimeBetween('-3 years', '-1 year'),
                'expire_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }
}

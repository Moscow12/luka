<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class WorkstationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workstation_name' => $this->faker->company(),
            'location' => $this->faker->randomElement(['Main Office', 'Branch Office', 'Remote', 'Satellite']),
            'address' => $this->faker->streetAddress(),
            'phone_number' => $this->faker->phoneNumber(),
            'tin_number' => $this->faker->numerify('##-#######'),
            'email_address' => $this->faker->companyEmail(),
            'physical_address' => $this->faker->streetAddress(),
            'region_id' => \App\Models\regions::factory(),
            'district_id' => \App\Models\districts::factory(),
            'ward_id' => \App\Models\wards::factory(),
            'country_id' => \App\Models\countries::factory(),
            'postal_code' => $this->faker->postcode(),
            'added_by'  => \App\Models\User::factory(),
        ];
    }
}

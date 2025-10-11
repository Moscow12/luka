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
            'Workstation_name' => $this->faker->company(),
            'StationLocation' => $this->faker->randomElement(['Main Office', 'Branch Office', 'Remote', 'Satellite']),
            'StationPhone_Number' => $this->faker->phoneNumber(),
            'Tin_Number' => $this->faker->numerify('##-#######'),
            'StationEmail_Address' => $this->faker->companyEmail(),
            'StationAddress' => $this->faker->streetAddress(),
            'StationCity' => $this->faker->city(),
            'StationProvince' => $this->faker->stateAbbr(),
            'StationCountry' => $this->faker->countryCode(),
            'StationPostalCode' => $this->faker->postcode(),
            'added_by'  => \App\Models\User::factory(),
        ];

        
    }
}

<?php

namespace Database\Factories;

use App\Models\Workstation;
use App\Models\User;
use App\Models\workstations;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkstationFactory extends Factory
{
    protected $model = workstations::class;

    public function definition()
    {
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'];
        $countries = ['USA', 'Canada', 'UK', 'Australia', 'Germany'];

        return [
            'name' => $this->faker->company() . ' Workstation',
            'location' => $this->faker->randomElement(['Main Office', 'Branch Office', 'Remote', 'Satellite']),
            'phone_number' => $this->faker->phoneNumber(),
            'tin_number' => $this->faker->numerify('##-#######'),
            'email_address' => $this->faker->companyEmail(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->stateAbbr(),
            'country' => $this->faker->countryCode(),
            'postal_code' => $this->faker->postcode(),
            'added_by' => User::factory(),
        ];
    }

    // State methods for specific scenarios
    public function mainOffice()
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => 'Main Office',
                'name' => 'Headquarters ' . $this->faker->word(),
            ];
        });
    }

    public function branchOffice()
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => 'Branch Office',
                'name' => $this->faker->city() . ' Branch',
            ];
        });
    }
}
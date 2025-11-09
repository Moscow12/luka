<?php

namespace Database\Factories;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use App\Models\User;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\workstations>
 */
class WorkstationsFactory extends Factory
{
    protected $model = workstations::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workstation_name' => $this->faker->company().' '.$this->faker->randomElement(['Branch', 'Office', 'Center', 'HQ']),
            'location' => $this->faker->randomElement(['Main Office', 'Downtown', 'Uptown', 'West Side', 'East Side', 'North Branch', 'South Branch']),
            'phone_number' => $this->faker->phoneNumber(),
            'tin_number' => $this->faker->numerify('##-#######'),
            'email_address' => $this->faker->unique()->companyEmail(),
            'postal_code' => $this->faker->postcode(),
            'physical_address' => $this->faker->streetAddress(),
            'country_id' => countries::inRandomOrder()->first()?->id ?? countries::factory(),
            'region_id' => regions::inRandomOrder()->first()?->id ?? regions::factory(),
            'district_id' => districts::inRandomOrder()->first()?->id ?? districts::factory(),
            'ward_id' => wards::inRandomOrder()->first()?->id ?? wards::factory(),
            'added_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\job_titles>
 */
class job_titlesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'code' => $this->faker->unique()->bothify('JT-###??'),
            'description' => $this->faker->sentence(),
            'added_by' => \App\Models\User::factory(),
        ];
    }
}

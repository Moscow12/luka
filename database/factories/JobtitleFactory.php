<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jobtitle>
 */
class JobtitleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobtitles = [
            'Medical Doctor',
            'Registered Nurse',
            'Laboratory Technician',
            'Radiologic Technologist',
            'Pharmacist',
            'Medical Administrator',
            'Department Head',
            'Specialist Consultant',
            'Medical Assistant',
            'Healthcare Assistant',
        ];

        return [
            'name' => $this->faker->randomElement($jobtitles),
            'code' => $this->faker->unique()->bothify('JT-###??'),
            'description' => $this->faker->sentence(),
            'added_by' => \App\Models\User::inRandomOrder()->first()?->id,
        ];
    }
}

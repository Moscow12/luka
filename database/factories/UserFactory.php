<?php

namespace Database\Factories;

use App\Models\Jobtitle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salutation' => fake()->randomElement(['Dr.', 'Mr.', 'Ms.', 'Mrs.', null]),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional(0.3)->firstName(),
            'surname' => fake()->lastName(),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'dob' => fake()->date('Y-m-d', '-25 years'),
            'address' => fake()->address(),
            'district' => fake()->city(),
            'region' => fake()->state(),
            'country' => 'Ghana',
            'postal_code' => fake()->postcode(),
            'jobtitle_id' => fake()->optional(0.8)->randomElement(Jobtitle::pluck('id')->toArray() ?: [Jobtitle::factory()->create()->id]),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'provider_type' => fake()->optional()->randomElement(['Doctor', 'Nurse', 'Technician', 'Administrator']),
            'reg_number' => fake()->optional()->bothify('REG-###-????'),
            'qualification' => fake()->optional()->randomElement(['MD', 'RN', 'BSN', 'MSN', 'PhD']),
            'remember_token' => Str::random(10),
        ];
    }
}

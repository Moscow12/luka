<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'first_name' => 'Admin',
            'surname' => 'User',
            'email' => 'admin@hospital.test',
            'username' => 'admin',
            'phone_number'=> '0717599994',
            'password' => Hash::make('admin@hospital.test'),
            'gender' => 'Male',
            'country' => 'Ghana',
        ]);

        // Create test users
        User::factory(10)->create();
    }
}

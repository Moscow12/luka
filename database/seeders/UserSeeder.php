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
        // Create super admin user
        User::create([
            'first_name' => 'System',
            'surname' => 'Administrator',
            'email' => 'admin@hospital.test',
            'username' => 'admin',
            'phone_number' => '0756077558',
            'password' => Hash::make('admin@hospital.test'),
            'gender' => 'Male',
            'country' => 'Tanzania',
            'is_super_admin' => true,
        ]);

        // Create test users
        User::factory(1)->create();
    }
}

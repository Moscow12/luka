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
        // Create or update super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hospital.test'],
            [
                'first_name' => 'System',
                'surname' => 'Administrator',
                'username' => 'admin',
                'phone_number' => '0756077558',
                'password' => Hash::make('admin@hospital.test'),
                'gender' => 'Male',
                'country' => 'Tanzania',
                'is_super_admin' => true,
            ]
        );

        // Ensure super admin flag is set
        if (! $admin->is_super_admin) {
            $admin->update(['is_super_admin' => true]);
        }

        $this->command->info("Super admin user: {$admin->email}");
    }
}

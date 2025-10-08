<?php

namespace Database\Seeders;

use App\Models\Workstation;
use App\Models\User;
use App\Models\workstations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkstationSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏢 Workstation Seeder');
        $this->command->line('=====================');

        // Check if there are users for the added_by field
        $userCount = User::count();
        if ($userCount === 0) {
            $this->command->error('No users found in database!');
            if ($this->command->confirm('Create a default user first?')) {
                User::factory()->create([
                    'name' => 'System Admin',
                    'email' => 'admin@example.com',
                ]);
                $this->command->info('✅ Default user created.');
            }
        }

        $seedingOption = $this->command->choice('How would you like to seed workstations?', [
            'quick_bulk',
            'interactive_custom',
            'sample_data',
            'skip_seeding'
        ], 'quick_bulk');

        switch ($seedingOption) {
            case 'quick_bulk':
                $this->quickBulkSeeding();
                break;
            case 'interactive_custom':
                $this->interactiveCustomSeeding();
                break;
            case 'sample_data':
                $this->sampleDataSeeding();
                break;
            case 'skip_seeding':
                $this->command->info('Skipping workstation seeding.');
                return;
        }

        $this->command->info('🎉 Workstation seeding completed!');
    }

    protected function quickBulkSeeding()
    {
        $count = (int) $this->command->ask('How many workstations do you want to create?', 5);
        
        $location = $this->command->choice('Primary location for these workstations?', [
            'New York', 'London', 'Tokyo', 'Sydney', 'Berlin', 'Toronto', 'Singapore', 'Paris'
        ], 'New York');

        $this->command->info("Creating {$count} workstations in {$location}...");

        $adminUser = User::first();

        $bar = $this->command->getOutput()->createProgressBar($count);

        workstations::factory()->count($count)->create([
            'location' => $location,
            'added_by' => $adminUser->id,
        ])->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} workstations created in {$location}!");
    }

    protected function interactiveCustomSeeding()
    {
        $workstations = [];

        while (true) {
            $this->command->info("\n--- Add New Workstation ---");

            $name = $this->command->ask('Workstation name');
            $location = $this->command->ask('Location', 'Main Office');
            $phoneNumber = $this->command->ask('Phone number', '+1-555-0100');
            $tinNumber = $this->command->ask('TIN number', '12-3456789');
            $emailAddress = $this->command->ask('Email address', "contact@{$name}.com");
            $address = $this->command->ask('Street address', '123 Main Street');
            $city = $this->command->ask('City', 'New York');
            $province = $this->command->ask('Province/State', 'NY');
            $country = $this->command->ask('Country', 'USA');
            $postalCode = $this->command->ask('Postal code', '10001');

            $adminUser = User::first();
            $addedBy = $adminUser ? $adminUser->id : 1;

            $workstations[] = [
                'name' => $name,
                'location' => $location,
                'phone_number' => $phoneNumber,
                'tin_number' => $tinNumber,
                'email_address' => $emailAddress,
                'address' => $address,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'postal_code' => $postalCode,
                'added_by' => $addedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->command->info("✅ Workstation '{$name}' added!");

            if (!$this->command->confirm('Add another workstation?')) {
                break;
            }
        }

        if (!empty($workstations)) {
            DB::table('workstations')->insert($workstations);
            $this->command->info("🎉 Successfully created " . count($workstations) . " workstations!");
        }
    }

    protected function sampleDataSeeding()
    {
        $sampleWorkstations = [
            [
                'name' => 'Headquarters',
                'location' => 'Main Building',
                'phone_number' => '+1-555-1000',
                'tin_number' => '11-2233445',
                'email_address' => 'hq@company.com',
                'address' => '123 Corporate Avenue',
                'city' => 'New York',
                'province' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
            ],
            [
                'name' => 'Downtown Branch',
                'location' => 'City Center',
                'phone_number' => '+1-555-1001',
                'tin_number' => '11-2233446',
                'email_address' => 'downtown@company.com',
                'address' => '456 Business Street',
                'city' => 'New York',
                'province' => 'NY',
                'country' => 'USA',
                'postal_code' => '10002',
            ],
            [
                'name' => 'Westside Office',
                'location' => 'West District',
                'phone_number' => '+1-555-1002',
                'tin_number' => '11-2233447',
                'email_address' => 'west@company.com',
                'address' => '789 Innovation Road',
                'city' => 'Los Angeles',
                'province' => 'CA',
                'country' => 'USA',
                'postal_code' => '90210',
            ],
            [
                'name' => 'London UK Office',
                'location' => 'Europe HQ',
                'phone_number' => '+44-20-7946-0958',
                'tin_number' => 'GB-123456789',
                'email_address' => 'london@company.com',
                'address' => '1 Business Square',
                'city' => 'London',
                'province' => 'Greater London',
                'country' => 'United Kingdom',
                'postal_code' => 'SW1A 1AA',
            ]
        ];

        $this->command->info('Available sample workstations:');
        foreach ($sampleWorkstations as $index => $workstation) {
            $this->command->line("{$index}. {$workstation['name']} - {$workstation['city']}, {$workstation['country']}");
        }

        $choice = $this->command->choice(
            'Which workstations do you want to create?',
            ['all', 'select_specific', 'custom_count'],
            'all'
        );

        $workstationsToCreate = [];

        switch ($choice) {
            case 'all':
                $workstationsToCreate = $sampleWorkstations;
                break;

            case 'select_specific':
                $selected = $this->command->choice(
                    'Select workstations to create (comma-separated):',
                    array_map(function ($ws, $index) {
                        return "{$index}. {$ws['name']}";
                    }, $sampleWorkstations, array_keys($sampleWorkstations)),
                    null,
                    null,
                    true
                );

                foreach ($selected as $selection) {
                    $index = explode('.', $selection)[0];
                    $workstationsToCreate[] = $sampleWorkstations[$index];
                }
                break;

            case 'custom_count':
                $count = (int) $this->command->ask('How many sample workstations?', 2);
                $workstationsToCreate = array_slice($sampleWorkstations, 0, $count);
                break;
        }

        $adminUser = User::first();
        $addedBy = $adminUser ? $adminUser->id : 1;

        foreach ($workstationsToCreate as $workstation) {
            $workstation['added_by'] = $addedBy;
            $workstation['created_at'] = now();
            $workstation['updated_at'] = now();

            workstations::create($workstation);
            $this->command->info("✅ Created: {$workstation['name']}");
        }

        $this->command->info("🎉 Created " . count($workstationsToCreate) . " sample workstations!");
    }
}
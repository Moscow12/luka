<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workstations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkstationsSeeder extends Seeder
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
                    'workstation_name' => 'System Admin',
                    'email_address' => 'admin@example.com',
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

        Workstations::factory()->count($count)->create([ 
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

            $workstation_name = $this->command->ask('Workstation name');
            $location = $this->command->ask('Location', 'Main Office');
            $phone_number = $this->command->ask('Phone number', '+1-555-0100');
            $tin_number = $this->command->ask('TIN number', '12-3456789');
            $email_address = $this->command->ask('Email address', "contact@{$workstation_name}.com");
            $physical_address = $this->command->ask('Street address', '123 Main Street');
            $region_id = $this->command->ask('City', 'New York');
            $district_id = $this->command->ask('District', 'NY');
            $country_id = $this->command->ask('Country', 'USA');
            $postal_code = $this->command->ask('Postal code', '10001');
            $ward_id = $this->command->ask('Ward', '1');

            $adminUser = User::first();
            $addedBy = $adminUser ? $adminUser->id : 1;

            $workstations[] = [
                'workstation_name' => $workstation_name,
                'location' => $location,
                'phone_number' => $phone_number,
                'tin_number' => $tin_number,
                'email_address' => $email_address,
                'physical_address' => $physical_address,
                'region_id' => $region_id,
                'district_id' => $district_id,
                'country_id' => $country_id,
                'ward_id' => $ward_id,
                'postal_code' => $postal_code,
                'added_by' => $addedBy,
                'created_at' => now(),
            ];

            $this->command->info("✅ Workstation '{$workstation_name}' added!");

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
                'workstation_name' => 'Headquarters',
                'location' => 'Main Building',
                'phone_number' => '+1-555-1000',
                'tin_number' => '11-2233445',
                'email_address' => 'hq@company.com',
                'physical_address' => '123 Corporate Avenue',
                'region_id' => 'New York',
                'district_id' => 'NY',
                'country_id' => 'USA',
                'postal_code' => '10001',
            ],
            [
                'workstation_name' => 'Downtown Branch',
                'location' => 'City Center',
                'phone_number' => '+1-555-1001',
                'tin_number' => '11-2233446',
                'email_address' => 'downtown@company.com',
                'physical_address' => '456 Business Street',
                'region_id' => 'New York',
                'district_id' => 'NY',
                'country_id' => 'USA',
                'postal_code' => '10002',
            ],
            [
                'workstation_name' => 'Westside Office',
                'location' => 'West District',
                'phone_number' => '+1-555-1002',
                'tin_number' => '11-2233447',
                'email_address' => 'west@company.com',
                'physical_address' => '789 Innovation Road',
                'region_id' => 'Los Angeles',
                'district_id' => 'CA',
                'country_id' => 'USA',
                'postal_code' => '90210',
            ],
            [
                'workstation_name' => 'London UK Office',
                'location' => 'Europe HQ',
                'phone_number' => '+44-20-7946-0958',
                'tin_number' => 'GB-123456789',
                'email_address' => 'london@company.com',
                'physical_address' => '1 Business Square',
                'region_id' => 'London',
                'district_id' => 'Greater London',
                'country_id' => 'United Kingdom',
                'postal_code' => 'SW1A 1AA',
            ]
        ];

        $this->command->info('Available sample workstationWorkstations:');
        foreach ($sampleWorkstations as $index => $workstation) {
            $this->command->line("{$index}. {$workstation['workstation_name']} - {$workstation['region_id']}, {$workstation['country_id']}");
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
                        return "{$index}. {$ws['workstation_name']}";
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

            Workstations::create($workstation);
            $this->command->info("✅ Created: {$workstation['workstation_name']}");
        }

        $this->command->info("🎉 Created " . count($workstationsToCreate) . " sample workstations!");
    }
}
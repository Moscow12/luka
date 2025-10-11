<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\workstations;
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
                    'Workstation_name' => 'System Admin',
                    'StationEmail_Address' => 'admin@example.com',
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
        
        $StationLocation = $this->command->choice('Primary location for these workstations?', [
            'New York', 'London', 'Tokyo', 'Sydney', 'Berlin', 'Toronto', 'Singapore', 'Paris'
        ], 'New York');

        $this->command->info("Creating {$count} workstations in {$StationLocation}...");

        $adminUser = User::first();

        $bar = $this->command->getOutput()->createProgressBar($count);

        workstations::factory()->count($count)->create([
            'StationLocation' => $StationLocation,
            'added_by' => $adminUser->id,
        ])->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} workstations created in {$StationLocation}!");
    }

    protected function interactiveCustomSeeding()
    {
        $workstations = [];

        while (true) {
            $this->command->info("\n--- Add New Workstation ---");

            $Workstation_name = $this->command->ask('Workstation name');
            $StationLocation = $this->command->ask('Location', 'Main Office');
            $StationPhone_Number = $this->command->ask('Phone number', '+1-555-0100');
            $Tin_Number = $this->command->ask('TIN number', '12-3456789');
            $StationEmail_Address = $this->command->ask('Email address', "contact@{$Workstation_name}.com");
            $StationAddress = $this->command->ask('Street address', '123 Main Street');
            $StationCity = $this->command->ask('City', 'New York');
            $StationProvince = $this->command->ask('Province/State', 'NY');
            $StationCountry = $this->command->ask('Country', 'USA');
            $StationPostalCode = $this->command->ask('Postal code', '10001');

            $adminUser = User::first();
            $addedBy = $adminUser ? $adminUser->id : 1;

            $workstations[] = [
                'Workstation_name' => $Workstation_name,
                'StationLocation' => $StationLocation,
                'StationPhone_Number' => $StationPhone_Number,
                'Tin_Number' => $Tin_Number,
                'StationEmail_Address' => $StationEmail_Address,
                'StationAddress' => $StationAddress,
                'StationCity' => $StationCity,
                'StationProvince' => $StationProvince,
                'StationCountry' => $StationCountry,
                'StationPostalCode' => $StationPostalCode,
                'added_by' => $addedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->command->info("✅ Workstation '{$Workstation_name}' added!");

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
                'Workstation_name' => 'Headquarters',
                'StationLocation' => 'Main Building',
                'StationPhone_Number' => '+1-555-1000',
                'Tin_Number' => '11-2233445',
                'StationEmail_Address' => 'hq@company.com',
                'StationAddress' => '123 Corporate Avenue',
                'StationCity' => 'New York',
                'StationProvince' => 'NY',
                'StationCountry' => 'USA',
                'StationPostalCode' => '10001',
            ],
            [
                'Workstation_name' => 'Downtown Branch',
                'StationLocation' => 'City Center',
                'StationPhone_Number' => '+1-555-1001',
                'Tin_Number' => '11-2233446',
                'StationEmail_Address' => 'downtown@company.com',
                'StationAddress' => '456 Business Street',
                'StationCity' => 'New York',
                'StationProvince' => 'NY',
                'StationCountry' => 'USA',
                'StationPostalCode' => '10002',
            ],
            [
                'Workstation_name' => 'Westside Office',
                'StationLocation' => 'West District',
                'StationPhone_Number' => '+1-555-1002',
                'Tin_Number' => '11-2233447',
                'StationEmail_Address' => 'west@company.com',
                'StationAddress' => '789 Innovation Road',
                'StationCity' => 'Los Angeles',
                'StationProvince' => 'CA',
                'StationCountry' => 'USA',
                'StationPostalCode' => '90210',
            ],
            [
                'Workstation_name' => 'London UK Office',
                'StationLocation' => 'Europe HQ',
                'StationPhone_Number' => '+44-20-7946-0958',
                'Tin_Number' => 'GB-123456789',
                'StationEmail_Address' => 'london@company.com',
                'StationAddress' => '1 Business Square',
                'StationCity' => 'London',
                'StationProvince' => 'Greater London',
                'StationCountry' => 'United Kingdom',
                'StationPostalCode' => 'SW1A 1AA',
            ]
        ];

        $this->command->info('Available sample workstations:');
        foreach ($sampleWorkstations as $index => $workstation) {
            $this->command->line("{$index}. {$workstation['Workstation_name']} - {$workstation['StationCity']}, {$workstation['StationCountry']}");
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
                        return "{$index}. {$ws['Workstation_name']}";
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
            $this->command->info("✅ Created: {$workstation['Workstation_name']}");
        }

        $this->command->info("🎉 Created " . count($workstationsToCreate) . " sample workstations!");
    }
}
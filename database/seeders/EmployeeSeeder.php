<?php

namespace Database\Seeders;

use App\Models\countries;
use App\Models\denominations;
use App\Models\departments;
use App\Models\designations;
use App\Models\districts;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Models\regions;
use App\Models\User;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Employee Seeder');
        $this->command->line('==================');

        // Check for required dependencies
        $this->checkDependencies();

        $seedingOption = $this->command->choice('How would you like to seed employees?', [
            'quick_bulk',
            'sample_data',
            'skip_seeding',
        ], 'quick_bulk');

        switch ($seedingOption) {
            case 'quick_bulk':
                $this->quickBulkSeeding();
                break;
            case 'sample_data':
                $this->sampleDataSeeding();
                break;
            case 'skip_seeding':
                $this->command->info('Skipping employee seeding.');

                return;
        }

        $this->command->info('🎉 Employee seeding completed!');
    }

    protected function checkDependencies()
    {
        $missing = [];

        if (workstations::count() === 0) {
            $missing[] = 'workstations';
        }
        if (departments::count() === 0) {
            $missing[] = 'departments';
        }
        if (designations::count() === 0) {
            $missing[] = 'designations';
        }
        if (Jobtitle::count() === 0) {
            $missing[] = 'job titles';
        }
        if (denominations::count() === 0) {
            $missing[] = 'denominations';
        }
        if (countries::count() === 0) {
            $missing[] = 'countries';
        }
        if (regions::count() === 0) {
            $missing[] = 'regions';
        }
        if (districts::count() === 0) {
            $missing[] = 'districts';
        }
        if (wards::count() === 0) {
            $missing[] = 'wards';
        }
        // Note: vilstreet_id (streets) is nullable, so not required
        if (User::count() === 0) {
            $missing[] = 'users';
        }

        if (! empty($missing)) {
            $this->command->error('Missing required data: '.implode(', ', $missing));
            $this->command->info('Please seed these tables first before creating employees.');
            exit(1);
        }
    }

    protected function quickBulkSeeding()
    {
        $count = (int) $this->command->ask('How many employees do you want to create?', 10);

        $department = departments::inRandomOrder()->first();
        $this->command->info("Creating {$count} employees in {$department->name} department...");

        $bar = $this->command->getOutput()->createProgressBar($count);

        Employee::factory()->count($count)->create([
            'department_id' => $department->id,
        ])->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} employees created!");
    }

    protected function sampleDataSeeding()
    {
        $count = (int) $this->command->ask('How many sample employees?', 5);

        $this->command->info("Creating {$count} sample employees with varied data...");

        $statuses = ['Active', 'probation', 'suspended'];
        $employmentTypes = ['Full-time', 'Part-time'];

        $bar = $this->command->getOutput()->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            Employee::factory()->create([
                'status' => $statuses[array_rand($statuses)],
                'employment_type' => $employmentTypes[array_rand($employmentTypes)],
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} sample employees created!");
    }
}

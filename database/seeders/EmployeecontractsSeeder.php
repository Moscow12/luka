<?php

namespace Database\Seeders;

use App\Models\departments;
use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\Jobtitle;
use App\Models\User;
use App\Models\workstations;
use Illuminate\Database\Seeder;

class EmployeecontractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📄 Employee Contracts Seeder');
        $this->command->line('===========================');

        // Check for required dependencies
        $this->checkDependencies();

        $seedingOption = $this->command->choice('How would you like to seed employee contracts?', [
            'quick_bulk',
            'per_employee',
            'by_status',
            'skip_seeding',
        ], 'quick_bulk');

        switch ($seedingOption) {
            case 'quick_bulk':
                $this->quickBulkSeeding();
                break;
            case 'per_employee':
                $this->perEmployeeSeeding();
                break;
            case 'by_status':
                $this->byStatusSeeding();
                break;
            case 'skip_seeding':
                $this->command->info('Skipping employee contracts seeding.');

                return;
        }

        $this->command->info('🎉 Employee contracts seeding completed!');
    }

    protected function checkDependencies()
    {
        $missing = [];

        if (Employee::count() === 0) {
            $missing[] = 'employees';
        }
        if (workstations::count() === 0) {
            $missing[] = 'workstations';
        }
        if (departments::count() === 0) {
            $missing[] = 'departments';
        }
        if (Jobtitle::count() === 0) {
            $missing[] = 'job titles';
        }
        if (User::count() === 0) {
            $missing[] = 'users';
        }

        if (! empty($missing)) {
            $this->command->error('Missing required data: '.implode(', ', $missing));
            $this->command->info('Please seed these tables first before creating employee contracts.');
            exit(1);
        }
    }

    protected function quickBulkSeeding()
    {
        $count = (int) $this->command->ask('How many employee contracts do you want to create?', 20);

        $this->command->info("Creating {$count} employee contracts...");

        $bar = $this->command->getOutput()->createProgressBar($count);

        Employeecontracts::factory()->count($count)->create()->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} employee contracts created!");
    }

    protected function perEmployeeSeeding()
    {
        $employees = Employee::with('department')->limit(20)->get();

        if ($employees->isEmpty()) {
            $this->command->error('No employees found!');

            return;
        }

        $this->command->info('Available employees:');
        foreach ($employees as $index => $employee) {
            $dept = $employee->department->name ?? 'N/A';
            $this->command->line("{$index}. {$employee->first_name} {$employee->last_name} - {$dept}");
        }

        $selection = $this->command->choice(
            'How do you want to proceed?',
            ['all_employees', 'random_employees'],
            'random_employees'
        );

        if ($selection === 'all_employees') {
            $selectedEmployees = $employees;
        } else {
            $randomCount = (int) $this->command->ask('How many random employees?', 5);
            $selectedEmployees = $employees->random(min($randomCount, $employees->count()));
        }

        $contractsPerEmployee = (int) $this->command->ask('How many contracts per employee?', 1);

        $this->command->info("Creating {$contractsPerEmployee} contract(s) for {$selectedEmployees->count()} employees...");

        $bar = $this->command->getOutput()->createProgressBar($selectedEmployees->count() * $contractsPerEmployee);

        foreach ($selectedEmployees as $employee) {
            for ($i = 0; $i < $contractsPerEmployee; $i++) {
                Employeecontracts::factory()->create([
                    'employee_id' => $employee->id,
                    'department_id' => $employee->department_id,
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created contracts for {$selectedEmployees->count()} employees!");
    }

    protected function byStatusSeeding()
    {
        $this->command->info('Creating contracts by status...');

        $activeCount = (int) $this->command->ask('How many ACTIVE contracts?', 10);
        $temporaryCount = (int) $this->command->ask('How many TEMPORARY contracts?', 5);
        $expiredCount = (int) $this->command->ask('How many EXPIRED contracts?', 3);

        $total = $activeCount + $temporaryCount + $expiredCount;
        $bar = $this->command->getOutput()->createProgressBar($total);

        // Create active contracts
        if ($activeCount > 0) {
            Employeecontracts::factory()->active()->count($activeCount)->create()->each(function () use ($bar) {
                $bar->advance();
            });
        }

        // Create temporary contracts
        if ($temporaryCount > 0) {
            Employeecontracts::factory()->temporary()->count($temporaryCount)->create()->each(function () use ($bar) {
                $bar->advance();
            });
        }

        // Create expired contracts
        if ($expiredCount > 0) {
            Employeecontracts::factory()->expired()->count($expiredCount)->create()->each(function () use ($bar) {
                $bar->advance();
            });
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created {$activeCount} active, {$temporaryCount} temporary, and {$expiredCount} expired contracts!");
    }
}

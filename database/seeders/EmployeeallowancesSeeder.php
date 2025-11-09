<?php

namespace Database\Seeders;

use App\Models\allowances;
use App\Models\Employee;
use App\Models\Employeeallowances;
use App\Models\Employeesalaries;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeallowancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Employee Allowances Seeder');
        $this->command->line('==========================');

        // Check for required dependencies
        $this->checkDependencies();

        $seedingOption = $this->command->choice('How would you like to seed employee allowances?', [
            'quick_bulk',
            'per_employee',
            'skip_seeding',
        ], 'quick_bulk');

        switch ($seedingOption) {
            case 'quick_bulk':
                $this->quickBulkSeeding();
                break;
            case 'per_employee':
                $this->perEmployeeSeeding();
                break;
            case 'skip_seeding':
                $this->command->info('Skipping employee allowances seeding.');

                return;
        }

        $this->command->info('🎉 Employee allowances seeding completed!');
    }

    protected function checkDependencies()
    {
        $missing = [];

        if (Employee::count() === 0) {
            $missing[] = 'employees';
        }
        if (allowances::count() === 0) {
            $missing[] = 'allowances';
        }
        if (User::count() === 0) {
            $missing[] = 'users';
        }

        if (! empty($missing)) {
            $this->command->error('Missing required data: '.implode(', ', $missing));
            $this->command->info('Please seed these tables first before creating employee allowances.');
            exit(1);
        }

        // Salaries are optional but recommended
        if (Employeesalaries::count() === 0) {
            $this->command->warn('Warning: No employee salaries found. Allowances will be created without salary association.');
        }
    }

    protected function quickBulkSeeding()
    {
        $count = (int) $this->command->ask('How many employee allowances do you want to create?', 20);

        $this->command->info("Creating {$count} employee allowances...");

        $bar = $this->command->getOutput()->createProgressBar($count);

        Employeeallowances::factory()->count($count)->create()->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} employee allowances created!");
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

        $allowancesPerEmployee = (int) $this->command->ask('How many allowances per employee?', 2);

        $this->command->info("Creating {$allowancesPerEmployee} allowances for {$selectedEmployees->count()} employees...");

        $bar = $this->command->getOutput()->createProgressBar($selectedEmployees->count() * $allowancesPerEmployee);

        foreach ($selectedEmployees as $employee) {
            for ($i = 0; $i < $allowancesPerEmployee; $i++) {
                Employeeallowances::factory()->create([
                    'employee_id' => $employee->id,
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created allowances for {$selectedEmployees->count()} employees!");
    }
}

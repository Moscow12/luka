<?php

namespace Database\Seeders;

use App\Models\allowances;
use App\Models\ContractAllowance;
use App\Models\Employeecontracts;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractAllowancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Contract Allowances Seeder');
        $this->command->line('============================');

        // Check for required dependencies
        $this->checkDependencies();

        $seedingOption = $this->command->choice('How would you like to seed contract allowances?', [
            'quick_bulk',
            'per_contract',
            'skip_seeding',
        ], 'quick_bulk');

        switch ($seedingOption) {
            case 'quick_bulk':
                $this->quickBulkSeeding();
                break;
            case 'per_contract':
                $this->perContractSeeding();
                break;
            case 'skip_seeding':
                $this->command->info('Skipping contract allowances seeding.');

                return;
        }

        $this->command->info('🎉 Contract allowances seeding completed!');
    }

    protected function checkDependencies()
    {
        $missing = [];

        if (Employeecontracts::count() === 0) {
            $missing[] = 'employee contracts';
        }
        if (allowances::count() === 0) {
            $missing[] = 'allowances';
        }
        if (User::count() === 0) {
            $missing[] = 'users';
        }

        if (! empty($missing)) {
            $this->command->error('Missing required data: '.implode(', ', $missing));
            $this->command->info('Please seed these tables first before creating contract allowances.');
            exit(1);
        }
    }

    protected function quickBulkSeeding()
    {
        $count = (int) $this->command->ask('How many contract allowances do you want to create?', 20);

        $this->command->info("Creating {$count} contract allowances...");

        $bar = $this->command->getOutput()->createProgressBar($count);

        ContractAllowance::factory()->count($count)->create()->each(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ {$count} contract allowances created!");
    }

    protected function perContractSeeding()
    {
        $contracts = Employeecontracts::with('employee')->limit(20)->get();

        if ($contracts->isEmpty()) {
            $this->command->error('No employee contracts found!');

            return;
        }

        $this->command->info('Available contracts:');
        foreach ($contracts as $index => $contract) {
            $employeeName = $contract->employee->first_name.' '.$contract->employee->last_name ?? 'N/A';
            $this->command->line("{$index}. {$employeeName} - {$contract->contract_type} ({$contract->status})");
        }

        $selection = $this->command->choice(
            'How do you want to proceed?',
            ['all_contracts', 'random_contracts'],
            'random_contracts'
        );

        if ($selection === 'all_contracts') {
            $selectedContracts = $contracts;
        } else {
            $randomCount = (int) $this->command->ask('How many random contracts?', 5);
            $selectedContracts = $contracts->random(min($randomCount, $contracts->count()));
        }

        $allowancesPerContract = (int) $this->command->ask('How many allowances per contract?', 2);

        $this->command->info("Creating {$allowancesPerContract} allowances for {$selectedContracts->count()} contracts...");

        $bar = $this->command->getOutput()->createProgressBar($selectedContracts->count() * $allowancesPerContract);

        foreach ($selectedContracts as $contract) {
            for ($i = 0; $i < $allowancesPerContract; $i++) {
                ContractAllowance::factory()->create([
                    'contract_id' => $contract->id,
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created allowances for {$selectedContracts->count()} contracts!");
    }
}

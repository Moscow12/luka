<?php

namespace App\Console\Commands;

use App\Models\asset;
use App\Models\assetclass;
use App\Models\building;
use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportFromTshrpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tshrp
                            {--dry-run : Run without actually importing data}
                            {--only= : Import only specific tables (comma-separated): jobtitles,designations,departments,buildings,assetclasses,assets,employees}
                            {--show-skips : Show detailed skip reasons in output}
                            {--export-skips= : Export skip reasons to a file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from old TSHRP database to new HRP Dasher system';

    /**
     * ID mapping storage (old integer IDs -> new UUIDs)
     */
    protected array $idMaps = [
        'jobtitles' => [],
        'designations' => [],
        'departments' => [],
        'buildings' => [],
        'assetclasses' => [],
        'assets' => [],
        'regions' => [],
        'districts' => [],
    ];

    /**
     * Statistics tracking
     */
    protected array $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    /**
     * Skip reasons tracking
     */
    protected array $skipReasons = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting TSHRP Database Import...');
        $this->newLine();

        // Check database connection
        if (! $this->checkConnection()) {
            return Command::FAILURE;
        }

        // Get options
        $dryRun = $this->option('dry-run');
        $onlyTables = $this->option('only') ? explode(',', $this->option('only')) : null;

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No data will be saved');
            $this->newLine();
        }

        // Get admin user for added_by field
        $adminUser = User::first();
        if (! $adminUser) {
            $this->error('No users found in the system. Please create at least one user first.');

            return Command::FAILURE;
        }

        $this->info("Using user: {$adminUser->name} (ID: {$adminUser->id}) as 'added_by'");
        $this->newLine();

        // Import order (respecting foreign key dependencies)
        $importSequence = [
            'jobtitles' => 'Job Titles',
            'designations' => 'Designations',
            'departments' => 'Departments',
            'buildings' => 'Buildings',
            'assetclasses' => 'Asset Classes',
            'assets' => 'Assets',
            'employees' => 'Employees',
        ];

        foreach ($importSequence as $table => $label) {
            // Skip if only specific tables requested
            if ($onlyTables && ! in_array($table, $onlyTables)) {
                continue;
            }

            $this->info("📦 Importing {$label}...");
            $methodName = 'import'.Str::studly($table);

            if (method_exists($this, $methodName)) {
                $this->$methodName($adminUser->id, $dryRun);
            }

            $this->newLine();
        }

        // Display summary
        $this->displaySummary();

        return Command::SUCCESS;
    }

    /**
     * Check database connection
     */
    protected function checkConnection(): bool
    {
        try {
            DB::connection('tshrp')->getPdo();
            $this->info('✅ Connected to TSHRP database');
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect to TSHRP database');
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn('Please configure TSHRP database credentials in your .env file:');
            $this->line('TSHRP_DB_HOST=127.0.0.1');
            $this->line('TSHRP_DB_PORT=3306');
            $this->line('TSHRP_DB_DATABASE=tshrp');
            $this->line('TSHRP_DB_USERNAME=root');
            $this->line('TSHRP_DB_PASSWORD=your_password');

            return false;
        }
    }

    /**
     * Import Job Titles
     */
    protected function importJobtitles(string $adminUserId, bool $dryRun): void
    {
        $oldTitles = DB::connection('tshrp')
            ->table('tbl_employee_title')
            ->get();

        if ($oldTitles->isEmpty()) {
            $this->warn('  No job titles found in TSHRP database');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldTitles->count());
        $progressBar->start();

        foreach ($oldTitles as $oldTitle) {
            try {
                // Check if already exists by name
                $existing = Jobtitle::where('name', $oldTitle->Employee_Title)->first();

                if ($existing) {
                    $this->idMaps['jobtitles'][$oldTitle->Title_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $this->addSkipReason("Job Title '{$oldTitle->Employee_Title}': Already exists");
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $newTitle = Jobtitle::create([
                        'name' => $oldTitle->Employee_Title,
                        'code' => $oldTitle->Job_code ?? Str::slug($oldTitle->Employee_Title),
                        'description' => null,
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['jobtitles'][$oldTitle->Title_ID] = $newTitle->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing title '{$oldTitle->Employee_Title}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Designations
     */
    protected function importDesignations(string $adminUserId, bool $dryRun): void
    {
        $oldDesignations = DB::connection('tshrp')
            ->table('tbl_desagnation')
            ->get();

        if ($oldDesignations->isEmpty()) {
            $this->warn('  No designations found in TSHRP database');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldDesignations->count());
        $progressBar->start();

        foreach ($oldDesignations as $oldDesignation) {
            try {
                // Check if already exists by name
                $existing = designations::where('name', $oldDesignation->Desagnation_name)->first();

                if ($existing) {
                    $this->idMaps['designations'][$oldDesignation->Desagnation_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $newDesignation = designations::create([
                        'name' => $oldDesignation->Desagnation_name,
                        'code' => Str::slug($oldDesignation->Desagnation_name),
                        'status' => $oldDesignation->Desagnation_Status ?? 'Active',
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['designations'][$oldDesignation->Desagnation_ID] = $newDesignation->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing designation '{$oldDesignation->Desagnation_name}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Departments
     */
    protected function importDepartments(string $adminUserId, bool $dryRun): void
    {
        $oldDepartments = DB::connection('tshrp')
            ->table('tbl_facility_department')
            ->get();

        if ($oldDepartments->isEmpty()) {
            $this->warn('  No departments found in TSHRP database');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldDepartments->count());
        $progressBar->start();

        foreach ($oldDepartments as $oldDept) {
            try {
                // Check if already exists by name
                $existing = departments::where('name', $oldDept->Department_name)->first();

                if ($existing) {
                    $this->idMaps['departments'][$oldDept->Facility_dept_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $newDept = departments::create([
                        'name' => $oldDept->Department_name,
                        'description' => $oldDept->Department_description ?? null,
                        'status' => 'active',
                        'supervisor_title_id' => null, // Can be updated later if needed
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['departments'][$oldDept->Facility_dept_ID] = $newDept->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing department '{$oldDept->Department_name}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Buildings
     */
    protected function importBuildings(string $adminUserId, bool $dryRun): void
    {
        $oldBuildings = DB::connection('tshrp')
            ->table('tbl_buildings')
            ->get();

        if ($oldBuildings->isEmpty()) {
            $this->warn('  No buildings found in TSHRP database');

            return;
        }

        // Get default workstation
        $defaultWorkstation = \App\Models\workstations::first();
        if (! $defaultWorkstation) {
            $this->error('  No workstations found. Please create at least one workstation first.');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldBuildings->count());
        $progressBar->start();

        foreach ($oldBuildings as $oldBuilding) {
            try {
                // Check if already exists by name
                $existing = building::where('name', $oldBuilding->BuildingName)->first();

                if ($existing) {
                    $this->idMaps['buildings'][$oldBuilding->Building_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $newBuilding = building::create([
                        'name' => $oldBuilding->BuildingName,
                        'workstation_id' => $defaultWorkstation->id,
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['buildings'][$oldBuilding->Building_ID] = $newBuilding->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing building '{$oldBuilding->BuildingName}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Asset Classes
     */
    protected function importAssetclasses(string $adminUserId, bool $dryRun): void
    {
        $oldAssetClasses = DB::connection('tshrp')
            ->table('tbl_Asset_class')
            ->get();

        if ($oldAssetClasses->isEmpty()) {
            $this->warn('  No asset classes found in TSHRP database');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldAssetClasses->count());
        $progressBar->start();

        foreach ($oldAssetClasses as $oldClass) {
            try {
                // Check if already exists by name
                $existing = assetclass::where('name', $oldClass->Asset_Class_Name)->first();

                if ($existing) {
                    $this->idMaps['assetclasses'][$oldClass->Asset_Class_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $depreciation = $oldClass->Asset_Depreciation ?? '0';
                    if (empty(trim($depreciation))) {
                        $depreciation = '0';
                    }

                    $newClass = assetclass::create([
                        'name' => $oldClass->Asset_Class_Name ?: 'Unnamed Asset Class',
                        'depreciation' => $depreciation,
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['assetclasses'][$oldClass->Asset_Class_ID] = $newClass->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing asset class '{$oldClass->Asset_Class_Name}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Assets
     */
    protected function importAssets(string $adminUserId, bool $dryRun): void
    {
        $oldAssets = DB::connection('tshrp')
            ->table('tbl_assets_items')
            ->get();

        if ($oldAssets->isEmpty()) {
            $this->warn('  No assets found in TSHRP database');

            return;
        }

        // Get default asset class if asset_class_id is missing
        $defaultAssetClass = assetclass::first();
        if (! $defaultAssetClass) {
            $this->error('  No asset classes found. Please import asset classes first.');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldAssets->count());
        $progressBar->start();

        foreach ($oldAssets as $oldAsset) {
            try {
                // Check if already exists by name
                $existing = asset::where('name', $oldAsset->Asset_Name)->first();

                if ($existing) {
                    $this->idMaps['assets'][$oldAsset->Asset_ID] = $existing->id;
                    $this->stats['skipped']++;
                    $progressBar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $newAsset = asset::create([
                        'name' => $oldAsset->Asset_Name,
                        'type' => $oldAsset->Asset_Type ?? 'current',
                        'asset_class_id' => $defaultAssetClass->id,
                        'added_by' => $adminUserId,
                    ]);

                    $this->idMaps['assets'][$oldAsset->Asset_ID] = $newAsset->id;
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing asset '{$oldAsset->Asset_Name}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Import Employees
     */
    protected function importEmployees(string $adminUserId, bool $dryRun): void
    {
        $this->warn('  ⚠️  Employee import requires proper mapping of all foreign keys.');
        $this->warn('  Please ensure all related data (departments, titles, locations, etc.) are imported first.');

        if (! $this->confirm('  Do you want to continue with employee import?', false)) {
            $this->info('  Skipping employee import.');

            return;
        }

        $oldEmployees = DB::connection('tshrp')
            ->table('tbl_employee_registration')
            ->get();

        if ($oldEmployees->isEmpty()) {
            $this->warn('  No employees found in TSHRP database');

            return;
        }

        // Get required defaults
        $defaults = $this->getEmployeeDefaults();
        if (! $defaults) {
            $this->error('  Missing required data. Cannot import employees.');

            return;
        }

        $progressBar = $this->output->createProgressBar($oldEmployees->count());
        $progressBar->start();

        foreach ($oldEmployees as $oldEmp) {
            try {
                // Check if already exists by employee number or email
                $existingByEmpNo = Employee::where('employee_no', $oldEmp->Emp_RER_NO)->first();
                $existingByEmail = ! empty($oldEmp->email_address)
                    ? Employee::where('email', $oldEmp->email_address)->first()
                    : null;

                if ($existingByEmpNo) {
                    $this->stats['skipped']++;
                    $this->addSkipReason("Employee #{$oldEmp->Emp_RER_NO} ({$oldEmp->Full_name}): Already exists with same employee number");
                    $progressBar->advance();

                    continue;
                }

                if ($existingByEmail) {
                    $this->stats['skipped']++;
                    $this->addSkipReason("Employee #{$oldEmp->Emp_RER_NO} ({$oldEmp->Full_name}): Already exists with same email");
                    $progressBar->advance();

                    continue;
                }

                // Parse full name
                $nameParts = $this->parseFullName($oldEmp->Full_name);

                // Map foreign keys
                $departmentId = $this->idMaps['departments'][$oldEmp->Desagnation_ID] ?? $defaults['department_id'];
                $titleId = $this->idMaps['jobtitles'][$oldEmp->title] ?? $defaults['title_id'];
                $designationId = $this->idMaps['designations'][$oldEmp->Desagnation_ID] ?? $defaults['designation_id'];

                if (! $dryRun) {
                    Employee::create([
                        'added_by' => $adminUserId,
                        'employee_no' => $oldEmp->Emp_RER_NO,
                        'first_name' => $nameParts['first_name'],
                        'middle_name' => $nameParts['middle_name'],
                        'last_name' => $nameParts['last_name'],
                        'gender' => ucfirst($oldEmp->Gender),
                        'dob' => $oldEmp->Date_of_birth,
                        'phone' => $oldEmp->Phone_number,
                        'email' => $oldEmp->email_address,
                        'education_level' => $this->mapEducationLevel($oldEmp->Education_Level),
                        'marital_status' => $this->mapMaritalStatus($oldEmp->MaritalStatus),
                        'department_id' => $departmentId,
                        'title_id' => $titleId,
                        'designation_id' => $designationId,
                        'country_id' => $defaults['country_id'],
                        'region_id' => $defaults['region_id'],
                        'district_id' => $defaults['district_id'],
                        'ward_id' => $defaults['ward_id'],
                        'workstation_id' => $defaults['workstation_id'],
                        'denomination_id' => $defaults['denomination_id'],
                        'status' => 'Active',
                        'hired_date' => now(),
                    ]);
                }

                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("  Error importing employee '{$oldEmp->Full_name}': ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ Imported: {$this->stats['imported']}, Skipped: {$this->stats['skipped']}, Errors: {$this->stats['errors']}");
        $this->resetStats();
    }

    /**
     * Get default values for employee import
     */
    protected function getEmployeeDefaults(): ?array
    {
        $defaults = [];

        $defaults['department_id'] = departments::first()?->id;
        $defaults['title_id'] = Jobtitle::first()?->id;
        $defaults['designation_id'] = designations::first()?->id;
        $defaults['country_id'] = \App\Models\countries::first()?->id;
        $defaults['region_id'] = \App\Models\regions::first()?->id;
        $defaults['district_id'] = \App\Models\districts::first()?->id;
        $defaults['ward_id'] = \App\Models\wards::first()?->id;
        $defaults['workstation_id'] = \App\Models\workstations::first()?->id;
        $defaults['denomination_id'] = \App\Models\denominations::first()?->id;

        foreach ($defaults as $key => $value) {
            if (! $value) {
                $this->error("  Missing required data: {$key}");

                return null;
            }
        }

        return $defaults;
    }

    /**
     * Parse full name into first, middle, last
     */
    protected function parseFullName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));

        return [
            'first_name' => $parts[0] ?? 'Unknown',
            'middle_name' => count($parts) > 2 ? $parts[1] : null,
            'last_name' => count($parts) > 1 ? end($parts) : 'Unknown',
        ];
    }

    /**
     * Map education level from old system to new
     */
    protected function mapEducationLevel(?string $oldLevel): ?string
    {
        $mapping = [
            'Primary' => 'Primary',
            'Secondary' => 'Certificate',
            'Diploma' => 'Diploma',
            'Certificate' => 'Certificate',
            'Degree' => 'Degree',
            'Bachelor' => 'Degree',
            'Masters' => 'Masters',
            'Master' => 'Masters',
            'PhD' => 'PhD',
            'Doctorate' => 'PhD',
        ];

        return $mapping[$oldLevel] ?? null;
    }

    /**
     * Map marital status from old system to new
     */
    protected function mapMaritalStatus(?string $oldStatus): string
    {
        if (empty($oldStatus)) {
            return 'Single';
        }

        $mapping = [
            'Single' => 'Single',
            'Married' => 'Married',
            'Merried' => 'Married', // Handle typo from old database
            'Divorced' => 'Divorced',
            'Widowed' => 'Widowed',
            'Widow' => 'Widowed',
            'Separated' => 'Separated',
            'Never married' => 'Never married',
            'Not applicable' => 'Not applicable',
        ];

        foreach ($mapping as $key => $value) {
            if (stripos($oldStatus, $key) !== false) {
                return $value;
            }
        }

        return 'Single'; // Default fallback
    }

    /**
     * Add skip reason
     */
    protected function addSkipReason(string $reason): void
    {
        $this->skipReasons[] = $reason;
    }

    /**
     * Display import summary
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Import Summary');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        foreach (['jobtitles', 'designations', 'departments', 'buildings', 'assetclasses', 'assets'] as $table) {
            $count = count($this->idMaps[$table]);
            if ($count > 0) {
                $this->line("  {$table}: {$count} ID mappings created");
            }
        }

        // Display skip reasons if any
        if (! empty($this->skipReasons)) {
            $this->newLine();
            $this->warn('⚠️  Skip Reasons ('.count($this->skipReasons).' total):');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // Check if user wants to see all skip reasons
            $showSkips = $this->option('show-skips');
            $displayLimit = $showSkips ? count($this->skipReasons) : 20;

            // Show skip reasons up to limit
            $displayReasons = array_slice($this->skipReasons, 0, $displayLimit);
            foreach ($displayReasons as $reason) {
                $this->line("  • {$reason}");
            }

            if (count($this->skipReasons) > $displayLimit) {
                $remaining = count($this->skipReasons) - $displayLimit;
                $this->line("  ... and {$remaining} more (use --show-skips to see all)");
            }

            // Export skip reasons to file if requested
            $exportFile = $this->option('export-skips');
            if ($exportFile) {
                $this->exportSkipReasons($exportFile);
            }
        }

        $this->newLine();
        $this->info('✅ Import completed!');
    }

    /**
     * Export skip reasons to file
     */
    protected function exportSkipReasons(string $filePath): void
    {
        try {
            $content = 'TSHRP Import Skip Reasons - '.now()->format('Y-m-d H:i:s')."\n";
            $content .= str_repeat('=', 80)."\n\n";
            $content .= 'Total skipped: '.count($this->skipReasons)."\n\n";

            foreach ($this->skipReasons as $index => $reason) {
                $content .= ($index + 1).". {$reason}\n";
            }

            file_put_contents($filePath, $content);
            $this->info("  📄 Skip reasons exported to: {$filePath}");
        } catch (\Exception $e) {
            $this->error('  Failed to export skip reasons: '.$e->getMessage());
        }
    }

    /**
     * Reset statistics
     */
    protected function resetStats(): void
    {
        $this->stats = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];
    }
}

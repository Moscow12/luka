<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\countries;
use App\Models\denominations;
use App\Models\departments;
use App\Models\designations;
use App\Models\districts;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Models\regions;
use App\Models\street;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Importstaffs extends Component
{
    use WithFileUploads;

    public $file;

    public array $previewData = [];

    public array $validationErrors = [];

    public bool $showPreview = false;

    public int $totalRows = 0;

    public int $validRows = 0;

    public int $invalidRows = 0;

    public bool $isImporting = false;

    public int $importedCount = 0;

    public array $importErrors = [];

    // Lookup caches
    protected array $departmentsCache = [];

    protected array $jobtitlesCache = [];

    protected array $designationsCache = [];

    protected array $countriesCache = [];

    protected array $regionsCache = [];

    protected array $districtsCache = [];

    protected array $wardsCache = [];

    protected array $workstationsCache = [];

    protected array $denominationsCache = [];

    protected array $streetsCache = [];

    // Required fields for import
    public array $requiredFields = [
        'employee_no' => 'Employee Number (unique identifier)',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'gender' => 'Gender (Male, Female, or Other)',
        'dob' => 'Date of Birth (YYYY-MM-DD)',
        'department' => 'Department Name',
        'job_title' => 'Job Title Name',
        'designation' => 'Designation Name',
        'country' => 'Country Name',
        'region' => 'Region Name',
        'district' => 'District Name',
        'ward' => 'Ward Name',
        'workstation' => 'Workstation Name',
        'denomination' => 'Denomination Name',
    ];

    public array $optionalFields = [
        'middle_name' => 'Middle Name',
        'national_id' => 'National ID (unique)',
        'phone' => 'Phone Number',
        'email' => 'Email Address',
        'employment_type' => 'Employment Type (Full-time, Part-time, Contract)',
        'hired_date' => 'Hire Date (YYYY-MM-DD)',
        'status' => 'Status (Active, Suspended, Terminated, Retired)',
        'education_level' => 'Education Level (Primary, Diploma, Certificate, Degree, Masters, PhD)',
        'marital_status' => 'Marital Status (Single, Married, Divorced, Widowed, Separated)',
        'tin_number' => 'TIN Number',
        'fpid' => 'Fingerprint ID',
        'street' => 'Street/Village Name',
    ];

    protected function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ];
    }

    public function updatedFile()
    {
        $this->validate();
        $this->parseFile();
    }

    protected function buildLookupCaches(): void
    {
        $this->departmentsCache = departments::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->jobtitlesCache = Jobtitle::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->designationsCache = designations::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->countriesCache = countries::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->regionsCache = regions::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->districtsCache = districts::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->wardsCache = wards::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->workstationsCache = workstations::pluck('id', 'workstation_name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->denominationsCache = denominations::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
        $this->streetsCache = street::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->toArray();
    }

    protected function parseFile(): void
    {
        $this->previewData = [];
        $this->validationErrors = [];
        $this->showPreview = false;
        $this->totalRows = 0;
        $this->validRows = 0;
        $this->invalidRows = 0;

        try {
            $this->buildLookupCaches();

            $data = Excel::toArray([], $this->file->getRealPath());

            if (empty($data) || empty($data[0])) {
                session()->flash('error', 'The uploaded file is empty.');

                return;
            }

            $rows = $data[0];
            $headers = array_map(fn ($h) => strtolower(trim(str_replace(' ', '_', $h))), $rows[0]);

            // Validate required columns exist
            $missingColumns = $this->validateColumns($headers);
            if (! empty($missingColumns)) {
                session()->flash('error', 'Missing required columns: '.implode(', ', $missingColumns));

                return;
            }

            // Process data rows
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $this->totalRows++;
                $rowData = array_combine($headers, $row);
                $processedRow = $this->processRow($rowData, $i + 1);

                $this->previewData[] = $processedRow;

                if (empty($processedRow['errors'])) {
                    $this->validRows++;
                } else {
                    $this->invalidRows++;
                }
            }

            $this->showPreview = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error parsing file: '.$e->getMessage());
        }
    }

    protected function validateColumns(array $headers): array
    {
        $requiredColumns = [
            'employee_no', 'first_name', 'last_name', 'gender', 'dob',
            'department', 'job_title', 'designation', 'country', 'region',
            'district', 'ward', 'workstation', 'denomination',
        ];

        return array_diff($requiredColumns, $headers);
    }

    protected function processRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $processed = [
            'row_number' => $rowNumber,
            'data' => [],
            'resolved' => [],
            'errors' => [],
        ];

        // Store original data
        $processed['data'] = $row;

        // Validate and resolve required fields
        $this->validateRequiredField($row, 'employee_no', $errors, $processed);
        $this->validateRequiredField($row, 'first_name', $errors, $processed);
        $this->validateRequiredField($row, 'last_name', $errors, $processed);

        // Validate gender
        if (empty($row['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (! in_array($row['gender'], ['Male', 'Female', 'Other'])) {
            $errors[] = "Invalid gender '{$row['gender']}'. Must be Male, Female, or Other";
        }

        // Validate date of birth
        if (empty($row['dob'])) {
            $errors[] = 'Date of birth is required';
        } else {
            $dob = $this->parseDate($row['dob']);
            if (! $dob) {
                $errors[] = "Invalid date format for dob: '{$row['dob']}'. Use YYYY-MM-DD";
            } else {
                $processed['resolved']['dob'] = $dob;
            }
        }

        // Resolve lookup fields
        $this->resolveLookup($row, 'department', $this->departmentsCache, 'department_id', $errors, $processed);
        $this->resolveLookup($row, 'job_title', $this->jobtitlesCache, 'title_id', $errors, $processed);
        $this->resolveLookup($row, 'designation', $this->designationsCache, 'designation_id', $errors, $processed);
        $this->resolveLookup($row, 'country', $this->countriesCache, 'country_id', $errors, $processed);
        $this->resolveLookup($row, 'region', $this->regionsCache, 'region_id', $errors, $processed);
        $this->resolveLookup($row, 'district', $this->districtsCache, 'district_id', $errors, $processed);
        $this->resolveLookup($row, 'ward', $this->wardsCache, 'ward_id', $errors, $processed);
        $this->resolveLookup($row, 'workstation', $this->workstationsCache, 'workstation_id', $errors, $processed);
        $this->resolveLookup($row, 'denomination', $this->denominationsCache, 'denomination_id', $errors, $processed);

        // Optional street lookup
        if (! empty($row['street'])) {
            $this->resolveLookup($row, 'street', $this->streetsCache, 'vilstreet_id', $errors, $processed, false);
        }

        // Validate optional fields
        if (! empty($row['hired_date'])) {
            $hiredDate = $this->parseDate($row['hired_date']);
            if (! $hiredDate) {
                $errors[] = "Invalid date format for hired_date: '{$row['hired_date']}'. Use YYYY-MM-DD";
            } else {
                $processed['resolved']['hired_date'] = $hiredDate;
            }
        }

        if (! empty($row['status']) && ! in_array($row['status'], ['Active', 'Suspended', 'Terminated', 'Retired'])) {
            $errors[] = "Invalid status '{$row['status']}'. Must be Active, Suspended, Terminated, or Retired";
        }

        if (! empty($row['education_level']) && ! in_array($row['education_level'], ['Primary', 'Diploma', 'Certificate', 'Degree', 'Masters', 'PhD'])) {
            $errors[] = "Invalid education_level '{$row['education_level']}'. Must be Primary, Diploma, Certificate, Degree, Masters, or PhD";
        }

        // Validate field lengths
        if (! empty($row['employee_no']) && strlen($row['employee_no']) > 50) {
            $errors[] = 'Employee number cannot exceed 50 characters';
        }

        if (! empty($row['national_id']) && strlen($row['national_id']) > 50) {
            $errors[] = 'National ID cannot exceed 50 characters';
        }

        if (! empty($row['tin_number']) && strlen($row['tin_number']) > 50) {
            $errors[] = 'TIN number cannot exceed 50 characters';
        }

        if (! empty($row['fpid']) && strlen($row['fpid']) > 50) {
            $errors[] = 'Fingerprint ID cannot exceed 50 characters';
        }

        if (! empty($row['phone']) && strlen($row['phone']) > 20) {
            $errors[] = 'Phone number cannot exceed 20 characters';
        }

        if (! empty($row['first_name']) && strlen($row['first_name']) > 100) {
            $errors[] = 'First name cannot exceed 100 characters';
        }

        if (! empty($row['last_name']) && strlen($row['last_name']) > 100) {
            $errors[] = 'Last name cannot exceed 100 characters';
        }

        // Check for duplicate employee_no in database
        if (! empty($row['employee_no']) && Employee::where('employee_no', $row['employee_no'])->exists()) {
            $errors[] = "Employee number '{$row['employee_no']}' already exists in database";
        }

        // Check for duplicate email in database
        if (! empty($row['email']) && Employee::where('email', $row['email'])->exists()) {
            $errors[] = "Email '{$row['email']}' already exists in database";
        }

        // Check for duplicate national_id in database
        if (! empty($row['national_id']) && Employee::where('national_id', $row['national_id'])->exists()) {
            $errors[] = "National ID '{$row['national_id']}' already exists in database";
        }

        $processed['errors'] = $errors;

        return $processed;
    }

    protected function validateRequiredField(array $row, string $field, array &$errors, array &$processed): void
    {
        if (empty($row[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)).' is required';
        } else {
            $processed['resolved'][$field] = trim($row[$field]);
        }
    }

    protected function resolveLookup(array $row, string $field, array $cache, string $resolvedKey, array &$errors, array &$processed, bool $required = true): void
    {
        $value = $row[$field] ?? '';

        if (empty($value)) {
            if ($required) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)).' is required';
            }

            return;
        }

        $key = strtolower(trim($value));

        if (isset($cache[$key])) {
            $processed['resolved'][$resolvedKey] = $cache[$key];
        } else {
            $errors[] = ucfirst(str_replace('_', ' ', $field))." '{$value}' not found in system";
        }
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Handle Excel serial date numbers
        if (is_numeric($value)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);

            return $date->format('Y-m-d');
        }

        // Try common date formats
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    public function downloadTemplate()
    {
        $headers = array_merge(
            array_keys($this->requiredFields),
            array_keys($this->optionalFields)
        );

        $sampleRow = [
            'EMP001',                    // employee_no
            'John',                      // first_name
            'Doe',                       // last_name
            'Male',                      // gender
            '1990-01-15',                // dob
            'Human Resources',           // department
            'Manager',                   // job_title
            'Senior',                    // designation
            'Tanzania',                  // country
            'Dar es Salaam',             // region
            'Ilala',                     // district
            'Kariakoo',                  // ward
            'Head Office',               // workstation
            'Catholic',                  // denomination
            'William',                   // middle_name
            '19900115-12345-00001-01',   // national_id
            '+255712345678',             // phone
            'john.doe@example.com',      // email
            'Full-time',                 // employment_type
            '2023-01-01',                // hired_date
            'Active',                    // status
            'Degree',                    // education_level
            'Single',                    // marital_status
            '123-456-789',               // tin_number
            'FP001',                     // fpid
            'Main Street',               // street
        ];

        $content = implode(',', $headers)."\n".implode(',', $sampleRow);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'staff_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadAvailableData()
    {
        $data = [
            ['Type', 'Available Values'],
            ['', ''],
            ['DEPARTMENTS', ''],
        ];

        foreach (departments::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['JOB TITLES', ''];
        foreach (Jobtitle::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['DESIGNATIONS', ''];
        foreach (designations::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['COUNTRIES', ''];
        foreach (countries::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['REGIONS', ''];
        foreach (regions::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['DISTRICTS', ''];
        foreach (districts::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['WARDS', ''];
        foreach (wards::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['WORKSTATIONS', ''];
        foreach (workstations::pluck('workstation_name') as $name) {
            $data[] = ['', $name];
        }

        $data[] = ['', ''];
        $data[] = ['DENOMINATIONS', ''];
        foreach (denominations::pluck('name') as $name) {
            $data[] = ['', $name];
        }

        $content = '';
        foreach ($data as $row) {
            $content .= implode(',', array_map(fn ($cell) => '"'.str_replace('"', '""', $cell).'"', $row))."\n";
        }

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'available_lookup_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function import()
    {
        if ($this->validRows === 0) {
            session()->flash('error', 'No valid rows to import.');

            return;
        }

        $this->isImporting = true;
        $this->importedCount = 0;
        $this->importErrors = [];

        DB::beginTransaction();

        try {
            foreach ($this->previewData as $row) {
                if (! empty($row['errors'])) {
                    continue;
                }

                $employeeData = [
                    'employee_no' => $row['resolved']['employee_no'],
                    'first_name' => $row['resolved']['first_name'],
                    'last_name' => $row['resolved']['last_name'],
                    'middle_name' => $row['data']['middle_name'] ?? null,
                    'gender' => $row['data']['gender'],
                    'dob' => $row['resolved']['dob'],
                    'national_id' => $row['data']['national_id'] ?? null,
                    'phone' => $row['data']['phone'] ?? null,
                    'email' => $row['data']['email'] ?? null,
                    'employment_type' => $row['data']['employment_type'] ?? null,
                    'hired_date' => $row['resolved']['hired_date'] ?? null,
                    'status' => $row['data']['status'] ?? 'Active',
                    'education_level' => $row['data']['education_level'] ?? null,
                    'marital_status' => $row['data']['marital_status'] ?? null,
                    'tin_number' => $row['data']['tin_number'] ?? null,
                    'fpid' => $row['data']['fpid'] ?? null,
                    'department_id' => $row['resolved']['department_id'],
                    'title_id' => $row['resolved']['title_id'],
                    'designation_id' => $row['resolved']['designation_id'],
                    'country_id' => $row['resolved']['country_id'],
                    'region_id' => $row['resolved']['region_id'],
                    'district_id' => $row['resolved']['district_id'],
                    'ward_id' => $row['resolved']['ward_id'],
                    'workstation_id' => $row['resolved']['workstation_id'],
                    'denomination_id' => $row['resolved']['denomination_id'],
                    'vilstreet_id' => $row['resolved']['vilstreet_id'] ?? null,
                    'added_by' => Auth::id(),
                ];

                Employee::create($employeeData);
                $this->importedCount++;
            }

            DB::commit();
            session()->flash('success', "Successfully imported {$this->importedCount} employees.");

            // Reset the form
            $this->reset(['file', 'previewData', 'showPreview', 'validationErrors', 'totalRows', 'validRows', 'invalidRows']);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Import failed: '.$e->getMessage());
        }

        $this->isImporting = false;
    }

    public function cancelImport()
    {
        $this->reset(['file', 'previewData', 'showPreview', 'validationErrors', 'totalRows', 'validRows', 'invalidRows']);
    }

    public function render()
    {
        return view('livewire.hr.staffs.importstaffs');
    }
}

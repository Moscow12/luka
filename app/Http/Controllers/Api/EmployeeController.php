<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSyncEntities;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    use ResolvesSyncEntities;

    /**
     * Register a new employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // The export script POSTs a bulk envelope ({action: bulk_register,
        // employees: [...]}) to this same endpoint. Detect it and fan out to
        // the per-employee registration logic.
        if ($request->input('action') === 'bulk_register' || is_array($request->input('employees'))) {
            return $this->bulkRegister($request);
        }

        $result = $this->registerOne($request->all());

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Register many employees in one request. Mirrors the response shape the
     * export script expects: { successful, failed, errors[] }.
     */
    public function bulkRegister(Request $request)
    {
        $rows = $request->input('employees', []);

        if (! is_array($rows) || empty($rows)) {
            return response()->json([
                'success' => false,
                'error_code' => 'EMPTY_BATCH',
                'message' => 'No employees provided in the bulk payload.',
            ], 422);
        }

        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $row = is_array($row) ? $row : [];
            $result = $this->registerOne($row);

            if ($result['status'] === 201) {
                $successful++;

                continue;
            }

            $failed++;
            $body = $result['body'];
            $errors[] = [
                'index' => $index,
                'employee_no' => $row['employee_no'] ?? null,
                'employee_name' => trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) ?: null,
                'error' => $body['error_summary'] ?? $body['message'] ?? 'Unknown error',
                'error_code' => $body['error_code'] ?? null,
                'http_code' => $result['status'],
                'detailed_errors' => $body['detailed_errors'] ?? null,
                'existing_employee' => $body['existing_employee'] ?? null,
            ];
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => 'Bulk employee registration completed',
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($rows),
            'errors' => $errors,
        ], 200);
    }

    /**
     * Register a single employee from a flat data array. Returns
     * ['status' => int, 'body' => array] so it can be reused by both the
     * single-employee endpoint and the bulk handler.
     */
    private function registerOne(array $data): array
    {
        try {
            // Check if employee already exists by phone or employee_no.
            // Only run the lookup when at least one identifier is present —
            // otherwise an empty where-closure would match the first row in
            // the table and report a bogus duplicate.
            $phone = trim((string) ($data['phone'] ?? ''));
            $employeeNoInput = trim((string) ($data['employee_no'] ?? ''));

            $existingEmployee = null;
            if ($phone !== '' || $employeeNoInput !== '') {
                $existingEmployee = Employee::where(function ($query) use ($phone, $employeeNoInput) {
                    if ($phone !== '') {
                        $query->orWhere('phone', $phone);
                    }
                    if ($employeeNoInput !== '') {
                        $query->orWhere('employee_no', $employeeNoInput);
                    }
                })->first();
            }

            if ($existingEmployee) {
                return ['status' => 200, 'body' => [
                    'success' => false,
                    'error_code' => 'EMPLOYEE_ALREADY_EXISTS',
                    'message' => 'Employee already exists in the system',
                    'error_summary' => "An employee with phone '{$phone}' or employee number '{$employeeNoInput}' already exists",
                    'matched_by' => [
                        'phone' => $existingEmployee->phone === $phone ? 'matched' : 'not matched',
                        'employee_no' => $existingEmployee->employee_no === $employeeNoInput ? 'matched' : 'not matched',
                    ],
                    'existing_employee' => [
                        'id' => $existingEmployee->id,
                        'employee_no' => $existingEmployee->employee_no,
                        'full_name' => $existingEmployee->first_name.' '.$existingEmployee->last_name,
                        'phone' => $existingEmployee->phone,
                        'email' => $existingEmployee->email,
                        'status' => $existingEmployee->status,
                    ],
                ]];
            }

            // Validate incoming data
            $validator = Validator::make($data, [
                // Core mandatory fields
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'required|in:Male,Female,Other',
                'dob' => 'required|date|before:today',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255|unique:employees,email',

                // Employment info
                'employment_type' => 'nullable|string',
                'hired_date' => 'nullable|date',
                'education_level' => 'nullable|string',
                'marital_status' => 'nullable|string',

                // Foreign keys - can be name or ID (now optional with fallbacks)
                'department' => 'nullable|string',
                'title' => 'nullable|string',
                'designation' => 'nullable|string',
                'workstation' => 'nullable|string',
                'denomination' => 'nullable|string',

                // Location - use default (Tanzania)
                'district' => 'nullable|string',

                // Optional fields
                'middle_name' => 'nullable|string|max:100',
                'national_id' => 'nullable|string|max:50|unique:employees,national_id',
                'employee_no' => 'nullable|string|max:50|unique:employees,employee_no',
                'tin_number' => 'nullable|string|max:50',
                'fpid' => 'nullable|string|max:50',
                'photo' => 'nullable|string',
                'signature' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::error('Employee Registration Validation Failed', [
                    'employee_id' => $data['employee_id'] ?? 'N/A',
                    'employee_name' => $data['employee_name'] ?? 'N/A',
                    'request_data' => $data,
                    'validation_errors' => $validator->errors(),
                ]);

                return ['status' => 422, 'body' => $this->validationErrorPayload($validator, 'Employee registration validation failed', [
                    'employee_id' => $data['employee_id'] ?? null,
                    'employee_name' => $data['employee_name'] ?? null,
                ])];
            }

            DB::beginTransaction();

            // Get first user for added_by field
            $firstUserId = DB::table('users')->first()->id ?? null;

            // Resolve or create foreign keys by name
            $departmentId = $this->resolveOrCreateDepartment($data['department'] ?? 'General', $firstUserId);
            $titleId = $this->resolveOrCreateJobTitle($data['title'] ?? null, $firstUserId);
            $designationId = $this->resolveOrCreateDesignation($data['designation'] ?? null, $firstUserId);
            $workstationId = $this->resolveWorkstation($data['workstation'] ?? null);
            $denominationId = $this->resolveOrCreateDenomination($data['denomination'] ?? 'Not Specified');

            // Get Tanzania as default country
            $country = DB::table('countries')->where('name', 'LIKE', '%Tanzania%')->first();
            if (! $country) {
                $country = DB::table('countries')->first();
            }

            // Get first region
            $region = DB::table('regions')->where('country_id', $country->id)->first();
            if (! $region) {
                $region = DB::table('regions')->first();
            }

            // Get district - either by name or first in region
            $district = null;
            if (! empty($data['district'])) {
                $district = DB::table('districts')
                    ->where('name', 'LIKE', '%'.$data['district'].'%')
                    ->first();
            }
            if (! $district) {
                $district = DB::table('districts')->where('region_id', $region->id)->first();
            }
            if (! $district) {
                $district = DB::table('districts')->first();
            }

            // Get first ward in district
            $ward = DB::table('wards')->where('district_id', $district->id)->first();
            if (! $ward) {
                $ward = DB::table('wards')->first();
            }

            // All IDs should now be resolved or created
            if (! $departmentId || ! $titleId || ! $designationId || ! $workstationId || ! $denominationId) {
                DB::rollBack();

                Log::error('Employee Registration Field Resolution Failed', [
                    'employee_id' => $data['employee_id'] ?? 'N/A',
                    'employee_name' => $data['employee_name'] ?? 'N/A',
                    'department' => $departmentId,
                    'title' => $titleId,
                    'designation' => $designationId,
                    'workstation' => $workstationId,
                    'denomination' => $denominationId,
                ]);

                $failedFields = [];
                if (! $departmentId) {
                    $failedFields[] = "department ('".($data['department'] ?? '')."')";
                }
                if (! $titleId) {
                    $failedFields[] = "title ('".($data['title'] ?? '')."')";
                }
                if (! $designationId) {
                    $failedFields[] = "designation ('".($data['designation'] ?? '')."')";
                }
                if (! $workstationId) {
                    $failedFields[] = "workstation ('".($data['workstation'] ?? '')."')";
                }
                if (! $denominationId) {
                    $failedFields[] = "denomination ('".($data['denomination'] ?? '')."')";
                }

                return ['status' => 422, 'body' => [
                    'success' => false,
                    'error_code' => 'FIELD_RESOLUTION_FAILED',
                    'message' => 'Failed to resolve or create required organizational fields',
                    'error_summary' => 'Could not find or create the following fields: '.implode(', ', $failedFields),
                    'failed_fields' => $failedFields,
                    'employee_id' => $data['employee_id'] ?? null,
                    'employee_name' => $data['employee_name'] ?? null,
                    'hint' => 'Please ensure the field names exist in the database or contact administrator',
                ]];
            }

            // Auto-generate employee number if not provided
            $employeeNo = ! empty($data['employee_no']) ? $data['employee_no'] : $this->generateEmployeeNumber();

            // Create employee
            $employee = Employee::create([
                'employee_no' => $employeeNo,
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'hired_date' => $data['hired_date'] ?? null,
                'status' => 'active',
                'education_level' => $data['education_level'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'department_id' => $departmentId,
                'title_id' => $titleId,
                'designation_id' => $designationId,
                'workstation_id' => $workstationId,
                'denomination_id' => $denominationId,
                'country_id' => $country->id,
                'region_id' => $region->id,
                'district_id' => $district->id,
                'ward_id' => $ward->id,
                'vilstreet_id' => null,
                'national_id' => $data['national_id'] ?? null,
                'tin_number' => $data['tin_number'] ?? null,
                'fpid' => $data['fpid'] ?? null,
                'photo' => $data['photo'] ?? null,
                'signature' => $data['signature'] ?? null,
                'added_by' => auth()->check() ? auth()->id() : ($data['added_by'] ?? (DB::table('users')->first()->id ?? null)),
            ]);

            DB::commit();

            return ['status' => 201, 'body' => [
                'success' => true,
                'message' => 'Employee registered successfully',
                'data' => [
                    'employee' => $employee->load(['department', 'designation', 'workstation']),
                ],
            ]];

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::error('Employee Registration Validation Exception', [
                'employee_id' => $data['employee_id'] ?? 'N/A',
                'employee_name' => $data['employee_name'] ?? 'N/A',
                'exception' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);

            return ['status' => 422, 'body' => [
                'success' => false,
                'error_code' => 'VALIDATION_EXCEPTION',
                'message' => 'Employee registration validation exception occurred',
                'error_summary' => $e->getMessage(),
                'detailed_errors' => $e->errors(),
                'employee_id' => $data['employee_id'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
            ]];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Registration Failed', [
                'employee_id' => $data['employee_id'] ?? 'N/A',
                'employee_name' => $data['employee_name'] ?? 'N/A',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['status' => 500, 'body' => [
                'success' => false,
                'error_code' => 'REGISTRATION_FAILED',
                'message' => 'Failed to register employee due to server error',
                'error_summary' => $e->getMessage(),
                'employee_id' => $data['employee_id'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
                'hint' => 'Please check the data format and try again, or contact administrator if the issue persists',
            ]];
        }
    }

    /**
     * Resolve or create job title
     */
    private function resolveOrCreateJobTitle(?string $name, ?string $addedBy): ?string
    {
        // If no name provided, pick random existing title
        if (empty($name)) {
            $random = DB::table('jobtitles')->inRandomOrder()->first();
            if ($random) {
                Log::info("Using random job title: {$random->name}", ['id' => $random->id]);

                return $random->id;
            }
        }

        // Try to find existing
        $existing = DB::table('jobtitles')
            ->where('name', 'LIKE', '%'.$name.'%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Try to create new job title
        try {
            $id = (string) Str::uuid();
            DB::table('jobtitles')->insert([
                'id' => $id,
                'name' => $name,
                'code' => strtoupper(substr($name, 0, 3)).rand(100, 999),
                'description' => 'Auto-created job title',
                'added_by' => $addedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Auto-created job title: {$name}", ['id' => $id]);

            return $id;
        } catch (\Exception $e) {
            // If creation fails, pick random existing title
            Log::warning("Failed to create job title '{$name}', picking random: {$e->getMessage()}");
            $random = DB::table('jobtitles')->inRandomOrder()->first();
            if ($random) {
                return $random->id;
            }

            return null;
        }
    }

    /**
     * Resolve or create designation
     */
    private function resolveOrCreateDesignation(?string $name, ?string $addedBy): ?string
    {
        // If no name provided, pick random existing designation
        if (empty($name)) {
            $random = DB::table('designations')->inRandomOrder()->first();
            if ($random) {
                Log::info("Using random designation: {$random->name}", ['id' => $random->id]);

                return $random->id;
            }
        }

        // Try to find existing
        $existing = DB::table('designations')
            ->where('name', 'LIKE', '%'.$name.'%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Try to create new designation
        try {
            $id = (string) Str::uuid();
            $code = strtoupper(substr(str_replace(' ', '', $name), 0, 5)).rand(10, 99);

            DB::table('designations')->insert([
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'status' => 'Active',
                'added_by' => $addedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Auto-created designation: {$name}", ['id' => $id]);

            return $id;
        } catch (\Exception $e) {
            // If creation fails, pick random existing designation
            Log::warning("Failed to create designation '{$name}', picking random: {$e->getMessage()}");
            $random = DB::table('designations')->inRandomOrder()->first();
            if ($random) {
                return $random->id;
            }

            return null;
        }
    }

    /**
     * Resolve a workstation to an existing one. Never creates a workstation:
     * if the requested name can't be matched, fall back to an existing
     * available workstation instead.
     */
    private function resolveWorkstation(?string $name): ?string
    {
        // Try to match the requested workstation by name.
        if (! empty($name)) {
            $existing = DB::table('workstations')
                ->where('workstation_name', 'LIKE', '%'.$name.'%')
                ->first();

            if ($existing) {
                return $existing->id;
            }

            Log::info("Workstation '{$name}' not found; falling back to an existing workstation.");
        }

        // No name given or no match: pick an existing available workstation.
        return DB::table('workstations')->first()->id ?? null;
    }

    /**
     * Resolve or create denomination
     */
    private function resolveOrCreateDenomination(string $name): ?string
    {
        // Try to find existing
        $existing = DB::table('denominations')
            ->where('name', 'LIKE', '%'.$name.'%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Get first religion or null
        $religion = DB::table('religions')->first();

        // Create new denomination
        $id = (string) Str::uuid();
        DB::table('denominations')->insert([
            'id' => $id,
            'name' => $name,
            'religion_id' => $religion->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Auto-created denomination: {$name}", ['id' => $id]);

        return $id;
    }

    /**
     * Generate unique employee number
     *
     * @return string
     */
    private function generateEmployeeNumber()
    {
        $year = date('Y');
        $lastEmployee = Employee::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastEmployee && preg_match('/STJH\/\d{4}\/(\d+)/', $lastEmployee->employee_no, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'STJH/'.$year.'/'.str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get all employees
     */
    public function index()
    {
        try {
            $employees = Employee::with(['department', 'designation', 'workstation'])
                ->where('status', 'active')
                ->latest()
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $employees,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single employee
     */
    public function show(string $id)
    {
        try {
            $employee = Employee::with([
                'department',
                'designation',
                'workstation',
                'position',
                'country',
                'region',
                'district',
                'ward',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $employee,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}

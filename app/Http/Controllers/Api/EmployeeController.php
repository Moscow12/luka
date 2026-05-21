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
        try {
            // Check if employee already exists by phone or employee_no
            $existingEmployee = Employee::where(function ($query) use ($request) {
                if ($request->phone) {
                    $query->where('phone', $request->phone);
                }
                if ($request->employee_no) {
                    $query->orWhere('employee_no', $request->employee_no);
                }
            })->first();

            if ($existingEmployee) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'EMPLOYEE_ALREADY_EXISTS',
                    'message' => 'Employee already exists in the system',
                    'error_summary' => "An employee with phone '{$request->phone}' or employee number '{$request->employee_no}' already exists",
                    'matched_by' => [
                        'phone' => $existingEmployee->phone === $request->phone ? 'matched' : 'not matched',
                        'employee_no' => $existingEmployee->employee_no === $request->employee_no ? 'matched' : 'not matched',
                    ],
                    'existing_employee' => [
                        'id' => $existingEmployee->id,
                        'employee_no' => $existingEmployee->employee_no,
                        'full_name' => $existingEmployee->first_name.' '.$existingEmployee->last_name,
                        'phone' => $existingEmployee->phone,
                        'email' => $existingEmployee->email,
                        'status' => $existingEmployee->status,
                    ],
                ], 200);
            }

            // Validate incoming request
            $validator = Validator::make($request->all(), [
                // Core mandatory fields
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'required|in:Male,Female,Other',
                'dob' => 'required|date|before:today',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:employees,email',

                // Employment info
                'employment_type' => 'required|in:Full-time,Part-time,Contract,Temporary',
                'hired_date' => 'required|date',
                'education_level' => 'required|in:Primary,Diploma,Certificate,Degree,Masters,PhD',
                'marital_status' => 'required|in:Single,Married,Divorced,Widowed,Separated,Never married,Not applicable',

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
                    'employee_id' => $request->employee_id ?? 'N/A',
                    'employee_name' => $request->employee_name ?? 'N/A',
                    'request_data' => $request->all(),
                    'validation_errors' => $validator->errors(),
                ]);

                return $this->validationErrorResponse($validator, 'Employee registration validation failed', [
                    'employee_id' => $request->employee_id ?? null,
                    'employee_name' => $request->employee_name ?? null,
                ]);
            }

            DB::beginTransaction();

            // Get first user for added_by field
            $firstUserId = DB::table('users')->first()->id ?? null;

            // Resolve or create foreign keys by name
            $departmentId = $this->resolveOrCreateDepartment($request->department ?? 'General', $firstUserId);
            $titleId = $this->resolveOrCreateJobTitle($request->title ?? null, $firstUserId);
            $designationId = $this->resolveOrCreateDesignation($request->designation ?? null, $firstUserId);
            $workstationId = $this->resolveWorkstation($request->workstation);
            $denominationId = $this->resolveOrCreateDenomination($request->denomination ?? 'Not Specified');

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
            if ($request->district) {
                $district = DB::table('districts')
                    ->where('name', 'LIKE', '%'.$request->district.'%')
                    ->first();
            }
            if (! isset($district) || ! $district) {
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
                    'employee_id' => $request->employee_id ?? 'N/A',
                    'employee_name' => $request->employee_name ?? 'N/A',
                    'department' => $departmentId,
                    'title' => $titleId,
                    'designation' => $designationId,
                    'workstation' => $workstationId,
                    'denomination' => $denominationId,
                ]);

                $failedFields = [];
                if (! $departmentId) {
                    $failedFields[] = "department ('{$request->department}')";
                }
                if (! $titleId) {
                    $failedFields[] = "title ('{$request->title}')";
                }
                if (! $designationId) {
                    $failedFields[] = "designation ('{$request->designation}')";
                }
                if (! $workstationId) {
                    $failedFields[] = "workstation ('{$request->workstation}')";
                }
                if (! $denominationId) {
                    $failedFields[] = "denomination ('{$request->denomination}')";
                }

                return response()->json([
                    'success' => false,
                    'error_code' => 'FIELD_RESOLUTION_FAILED',
                    'message' => 'Failed to resolve or create required organizational fields',
                    'error_summary' => 'Could not find or create the following fields: '.implode(', ', $failedFields),
                    'failed_fields' => $failedFields,
                    'employee_id' => $request->employee_id ?? null,
                    'employee_name' => $request->employee_name ?? null,
                    'hint' => 'Please ensure the field names exist in the database or contact administrator',
                ], 422);
            }

            // Auto-generate employee number if not provided
            $employeeNo = $request->employee_no ?? $this->generateEmployeeNumber();

            // Create employee
            $employee = Employee::create([
                'employee_no' => $employeeNo,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'email' => $request->email,
                'employment_type' => $request->employment_type,
                'hired_date' => $request->hired_date,
                'status' => 'active',
                'education_level' => $request->education_level,
                'marital_status' => $request->marital_status,
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
                'national_id' => $request->national_id,
                'tin_number' => $request->tin_number,
                'fpid' => $request->fpid,
                'photo' => $request->photo,
                'signature' => $request->signature,
                'added_by' => auth()->check() ? auth()->id() : $request->input('added_by', DB::table('users')->first()->id ?? null),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee registered successfully',
                'data' => [
                    'employee' => $employee->load(['department', 'designation', 'workstation']),
                ],
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::error('Employee Registration Validation Exception', [
                'employee_id' => $request->employee_id ?? 'N/A',
                'employee_name' => $request->employee_name ?? 'N/A',
                'exception' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'VALIDATION_EXCEPTION',
                'message' => 'Employee registration validation exception occurred',
                'error_summary' => $e->getMessage(),
                'detailed_errors' => $e->errors(),
                'employee_id' => $request->employee_id ?? null,
                'employee_name' => $request->employee_name ?? null,
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Registration Failed', [
                'employee_id' => $request->employee_id ?? 'N/A',
                'employee_name' => $request->employee_name ?? 'N/A',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'REGISTRATION_FAILED',
                'message' => 'Failed to register employee due to server error',
                'error_summary' => $e->getMessage(),
                'employee_id' => $request->employee_id ?? null,
                'employee_name' => $request->employee_name ?? null,
                'hint' => 'Please check the data format and try again, or contact administrator if the issue persists',
            ], 500);
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

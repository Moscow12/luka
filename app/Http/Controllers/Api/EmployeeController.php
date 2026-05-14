<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Register a new employee
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check if employee already exists by phone or employee_no
            $existingEmployee = Employee::where(function($query) use ($request) {
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
                    'message' => 'Employee already exists',
                    'data' => [
                        'employee' => $existingEmployee->load(['department', 'designation', 'workstation'])
                    ]
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

                // Foreign keys - can be name or ID
                'department' => 'required|string',
                'title' => 'required|string',
                'designation' => 'required|string',
                'workstation' => 'required|string',
                'denomination' => 'required|string',

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
                    'validation_errors' => $validator->errors()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'received_data' => $request->all()
                ], 422);
            }

            DB::beginTransaction();

            // Get first user for added_by field
            $firstUserId = DB::table('users')->first()->id ?? null;

            // Resolve or create foreign keys by name
            $departmentId = $this->resolveOrCreateDepartment($request->department, $firstUserId);
            $titleId = $this->resolveOrCreateJobTitle($request->title, $firstUserId);
            $designationId = $this->resolveOrCreateDesignation($request->designation, $firstUserId);
            $workstationId = $this->resolveOrCreateWorkstation($request->workstation, $firstUserId);
            $denominationId = $this->resolveOrCreateDenomination($request->denomination);

            // Get Tanzania as default country
            $country = DB::table('countries')->where('name', 'LIKE', '%Tanzania%')->first();
            if (!$country) {
                $country = DB::table('countries')->first();
            }

            // Get first region
            $region = DB::table('regions')->where('country_id', $country->id)->first();
            if (!$region) {
                $region = DB::table('regions')->first();
            }

            // Get district - either by name or first in region
            if ($request->district) {
                $district = DB::table('districts')
                    ->where('name', 'LIKE', '%' . $request->district . '%')
                    ->first();
            }
            if (!isset($district) || !$district) {
                $district = DB::table('districts')->where('region_id', $region->id)->first();
            }
            if (!$district) {
                $district = DB::table('districts')->first();
            }

            // Get first ward in district
            $ward = DB::table('wards')->where('district_id', $district->id)->first();
            if (!$ward) {
                $ward = DB::table('wards')->first();
            }

            // All IDs should now be resolved or created
            if (!$departmentId || !$titleId || !$designationId || !$workstationId || !$denominationId) {
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

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resolve or create required fields',
                    'error' => 'Could not create required database records'
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
                    'employee' => $employee->load(['department', 'designation', 'workstation'])
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve or create department
     */
    private function resolveOrCreateDepartment(string $name, ?string $addedBy): ?string
    {
        // Try to find existing
        $existing = DB::table('departments')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Get first job title for supervisor_title_id
        $firstJobTitle = DB::table('jobtitles')->first();

        // Create new department
        $id = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $id,
            'name' => $name,
            'description' => 'Auto-created department',
            'supervisor_title_id' => $firstJobTitle->id ?? null,
            'status' => 'active',
            'added_by' => $addedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Auto-created department: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create job title
     */
    private function resolveOrCreateJobTitle(string $name, ?string $addedBy): ?string
    {
        // Try to find existing
        $existing = DB::table('jobtitles')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Create new job title
        $id = (string) Str::uuid();
        DB::table('jobtitles')->insert([
            'id' => $id,
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)) . rand(100, 999),
            'description' => 'Auto-created job title',
            'added_by' => $addedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Auto-created job title: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create designation
     */
    private function resolveOrCreateDesignation(string $name, ?string $addedBy): ?string
    {
        // Try to find existing
        $existing = DB::table('designations')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Create new designation
        $id = (string) Str::uuid();
        $code = strtoupper(substr(str_replace(' ', '', $name), 0, 5)) . rand(10, 99);

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
    }

    /**
     * Resolve or create workstation
     */
    private function resolveOrCreateWorkstation(string $name, ?string $addedBy): ?string
    {
        // Try to find existing
        $existing = DB::table('workstations')
            ->where('workstation_name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Get default location data
        $country = DB::table('countries')->where('name', 'LIKE', '%Tanzania%')->first();
        if (!$country) {
            $country = DB::table('countries')->first();
        }

        $region = DB::table('regions')->where('country_id', $country->id)->first();
        if (!$region) {
            $region = DB::table('regions')->first();
        }

        $district = DB::table('districts')->where('region_id', $region->id)->first();
        if (!$district) {
            $district = DB::table('districts')->first();
        }

        $ward = DB::table('wards')->where('district_id', $district->id)->first();
        if (!$ward) {
            $ward = DB::table('wards')->first();
        }

        // Create new workstation
        $id = (string) Str::uuid();
        DB::table('workstations')->insert([
            'id' => $id,
            'workstation_name' => $name,
            'location' => 'Main Office',
            'phone_number' => '0000000000',
            'tin_number' => null,
            'email_address' => null,
            'postal_code' => null,
            'physical_address' => 'Auto-created',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'added_by' => $addedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Auto-created workstation: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create denomination
     */
    private function resolveOrCreateDenomination(string $name): ?string
    {
        // Try to find existing
        $existing = DB::table('denominations')
            ->where('name', 'LIKE', '%' . $name . '%')
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

        if ($lastEmployee && preg_match('/STIH\/\d{4}\/(\d+)/', $lastEmployee->employee_no, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'STIH/' . $year . '/' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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
                'data' => $employees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage()
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
                'ward'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $employee
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}

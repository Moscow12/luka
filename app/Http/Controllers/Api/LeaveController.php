<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employeeleaves;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LeaveController extends Controller
{
    /**
     * Receive leave from third-party system
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Remote exporters send "" for missing values; coerce to null before validating.
            $input = $this->normalizeInput($request->all());
            $request->replace($input);

            $validator = Validator::make($input, [
                'emp_leave_id'     => 'nullable|string|max:100',
                'employee_id'      => 'nullable|string|max:100',
                'employee_reg_no'  => 'required|string|max:100',
                'employee_name'    => 'nullable|string|max:255',
                'gender'           => 'nullable|string|max:20',
                'phone'            => 'nullable|string|max:20',
                'email'            => 'nullable|email|max:255',
                'national_id'      => 'nullable|string|max:50',
                'department'       => 'nullable|string|max:255',
                'leave_id'         => 'nullable|string|max:100',
                'leave_name'       => 'required|string|max:255',
                'leave_start_date' => 'required|date',
                // Real-world data has rejected/awaiting leaves with end < start;
                // accept any valid date and let downstream logic handle it.
                'leave_end_date'   => 'required|date',
                'travel_to'       => 'nullable|string|max:255',
                'EmployeeComments' => 'nullable|string|max:255',
                'number_of_days'   => 'nullable|numeric|min:0',
                'leave_status'     => 'nullable|string|max:50',
                'requested_at'     => 'nullable|date',
            ]);

            if ($validator->fails()) {
                Log::error('Leave Sync Validation Failed', [
                    'emp_leave_id'    => $request->emp_leave_id ?? 'N/A',
                    'employee_reg_no' => $request->employee_reg_no ?? 'N/A',
                    'request_data'    => $request->all(),
                    'validation_errors' => $validator->errors(),
                ]);

                $formattedErrors = [];
                $missingFields = [];
                $invalidFields = [];

                foreach ($validator->errors()->messages() as $field => $messages) {
                    $formattedErrors[$field] = $messages[0];

                    if (str_contains($messages[0], 'required')) {
                        $missingFields[] = $field;
                    } else {
                        $invalidFields[] = $field;
                    }
                }

                $errorSummary = [];
                if (!empty($missingFields)) {
                    $errorSummary[] = 'Missing required fields: ' . implode(', ', $missingFields);
                }
                if (!empty($invalidFields)) {
                    $errorSummary[] = 'Invalid field values: ' . implode(', ', $invalidFields);
                }

                return response()->json([
                    'success'         => false,
                    'error_code'      => 'VALIDATION_FAILED',
                    'message'         => 'Leave sync validation failed',
                    'error_summary'   => implode('. ', $errorSummary),
                    'detailed_errors' => $formattedErrors,
                    'total_errors'    => count($formattedErrors),
                    'emp_leave_id'    => $request->emp_leave_id ?? null,
                    'employee_reg_no' => $request->employee_reg_no ?? null,
                ], 422);
            }

            // Resolve employee by employee_no (Emp_RER_NO)
            $employee = Employee::where('employee_no', $request->employee_reg_no)->first();

            if (!$employee) {
                Log::error('Leave Sync Employee Not Found', [
                    'employee_reg_no' => $request->employee_reg_no,
                    'employee_name'   => $request->employee_name,
                ]);

                return response()->json([
                    'success'         => false,
                    'error_code'      => 'EMPLOYEE_NOT_FOUND',
                    'message'         => 'Employee not found in the system',
                    'error_summary'   => "No employee found with registration number '{$request->employee_reg_no}'",
                    'employee_reg_no' => $request->employee_reg_no,
                    'employee_name'   => $request->employee_name,
                    'hint'            => 'Please register the employee first before syncing their leaves',
                ], 404);
            }

            DB::beginTransaction();

            // Get first user for added_by field
            $firstUserId = DB::table('users')->first()->id ?? null;

            // Calculate number of days if not provided. Use absolute diff so
            // rejected leaves with end < start still produce a positive count.
            $numberOfDays = $request->number_of_days;
            if (empty($numberOfDays)) {
                $start = \Carbon\Carbon::parse($request->leave_start_date);
                $end = \Carbon\Carbon::parse($request->leave_end_date);
                $numberOfDays = abs($start->diffInDays($end)) + 1;
            }

            // Resolve or create leave type by name
            $leaveTypeId = $this->resolveOrCreateLeaveType(
                $request->leave_name,
                $numberOfDays,
                $request->gender,
                $firstUserId
            );

            if (!$leaveTypeId) {
                DB::rollBack();
                return response()->json([
                    'success'       => false,
                    'error_code'    => 'LEAVE_TYPE_RESOLUTION_FAILED',
                    'message'       => 'Failed to resolve or create leave type',
                    'error_summary' => "Could not find or create leave type '{$request->leave_name}'",
                    'leave_name'    => $request->leave_name,
                ], 422);
            }

            // Map third-party status to local status
            $status = $this->mapLeaveStatus($request->leave_status);

            // Check if leave already exists (by employee + leave type + start date)
            $existingLeave = Employeeleaves::where('employee_id', $employee->id)
                ->where('leave_id', $leaveTypeId)
                ->where('start_date', $request->leave_start_date)
                ->first();

            if ($existingLeave) {
                // Update existing leave
                $existingLeave->update([
                    'end_date' => $request->leave_end_date,
                    'days'     => $numberOfDays,
                    'status'   => $status,
                ]);

                DB::commit();

                return response()->json([
                    'success'       => false,
                    'error_code'    => 'LEAVE_ALREADY_EXISTS',
                    'message'       => 'Leave already exists, updated successfully',
                    'error_summary' => "A leave for employee '{$employee->employee_no}' with type '{$request->leave_name}' starting '{$request->leave_start_date}' already exists",
                    'data'          => [
                        'leave'    => $existingLeave->load(['employee', 'leave']),
                        'employee' => [
                            'id'          => $employee->id,
                            'employee_no' => $employee->employee_no,
                            'full_name'   => $employee->first_name . ' ' . $employee->last_name,
                        ],
                    ],
                ], 200);
            }

            // Create new employee leave
            $employeeLeave = Employeeleaves::create([
                'employee_id'  => $employee->id,
                'leave_id'     => $leaveTypeId,
                'start_date'   => $request->leave_start_date,
                'end_date'     => $request->leave_end_date,
                'days'         => $numberOfDays,
                'travel_to'    => $request->travel_to ?? 'N/A',
                'othercontact' => $request->phone ?? 'N/A',
                'comments'     => $request->EmployeeComments. 'Ref: ' . ($request->emp_leave_id ?? 'N/A'),
                'status'       => $status,
                'added_by'     => $firstUserId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave synced successfully',
                'data'    => [
                    'leave'    => $employeeLeave->load(['employee', 'leave']),
                    'employee' => [
                        'id'          => $employee->id,
                        'employee_no' => $employee->employee_no,
                        'full_name'   => $employee->first_name . ' ' . $employee->last_name,
                    ],
                ],
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::error('Leave Sync Validation Exception', [
                'emp_leave_id'    => $request->emp_leave_id ?? 'N/A',
                'employee_reg_no' => $request->employee_reg_no ?? 'N/A',
                'exception'       => $e->getMessage(),
                'errors'          => $e->errors(),
            ]);

            return response()->json([
                'success'         => false,
                'error_code'      => 'VALIDATION_EXCEPTION',
                'message'         => 'Leave sync validation exception occurred',
                'error_summary'   => $e->getMessage(),
                'detailed_errors' => $e->errors(),
                'emp_leave_id'    => $request->emp_leave_id ?? null,
                'employee_reg_no' => $request->employee_reg_no ?? null,
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Leave Sync Failed', [
                'emp_leave_id'    => $request->emp_leave_id ?? 'N/A',
                'employee_reg_no' => $request->employee_reg_no ?? 'N/A',
                'exception'       => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'         => false,
                'error_code'      => 'LEAVE_SYNC_FAILED',
                'message'         => 'Failed to sync leave due to server error',
                'error_summary'   => $e->getMessage(),
                'emp_leave_id'    => $request->emp_leave_id ?? null,
                'employee_reg_no' => $request->employee_reg_no ?? null,
                'hint'            => 'Please check the data format and try again, or contact administrator if the issue persists',
            ], 500);
        }
    }

    /**
     * Resolve or create leave type
     */
    private function resolveOrCreateLeaveType(string $name, int|float|string $days, ?string $gender, ?string $addedBy): ?string
    {
        $existing = DB::table('leaves')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $id = (string) Str::uuid();
        DB::table('leaves')->insert([
            'id'               => $id,
            'name'             => $name,
            'description'      => 'Auto-created from third-party sync',
            'days'             => (int) ceil((float) $days),
            'gender'           => 'Both',
            'status'           => 'active',
            'paid'             => false,
            'require_document' => false,
            'added_by'         => $addedBy,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        Log::info("Auto-created leave type: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Remote exporters ship "" for missing values and MySQL "0000-00-00" sentinels;
     * coerce empties to null and normalize dates so the `date`/`email`/`numeric`
     * validators don't reject perfectly valid payloads.
     */
    private function normalizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $input[$key] = null;
            }
        }

        $input['leave_start_date'] = $this->normalizeDate($input['leave_start_date'] ?? null);
        $input['leave_end_date']   = $this->normalizeDate($input['leave_end_date'] ?? null);
        $input['requested_at']     = $this->normalizeDate($input['requested_at'] ?? null);

        // If end_date is missing/invalid, fall back to start_date so the row can save.
        if ($input['leave_end_date'] === null && !empty($input['leave_start_date'])) {
            $input['leave_end_date'] = $input['leave_start_date'];
        }

        return $input;
    }

    /**
     * Accepts ISO, DD/MM/YYYY, DD-MM-YYYY, or MySQL zero-date sentinels.
     */
    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || $value === '' || str_starts_with($value, '0000-')) {
            return null;
        }

        try {
            if (preg_match('#^(\d{2})[/-](\d{2})[/-](\d{4})#', $value, $m)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}")->toDateString();
            }
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Map third-party leave status to local status
     */
    private function mapLeaveStatus(?string $status): string
    {
        $normalized = strtolower(trim($status ?? ''));

        return match ($normalized) {
            'approved', 'approve'   => 'approved',
            'rejected', 'reject'    => 'rejected',
            'active'                => 'active',
            'awaiting'              => 'awaiting',
            'pending', ''           => 'pending',
            default                 => $normalized,
        };
    }

    /**
     * Get all employee leaves
     */
    public function index()
    {
        try {
            $leaves = Employeeleaves::with(['employee', 'leave'])
                ->latest()
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data'    => $leaves,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaves',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single employee leave
     */
    public function show(string $id)
    {
        try {
            $leave = Employeeleaves::with(['employee', 'leave'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $leave,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}

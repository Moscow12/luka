<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSyncEntities;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employeecontracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    use ResolvesSyncEntities;

    /**
     * Register or update a contract. The export script POSTs either a single
     * flat row or a bulk envelope ({action: bulk_register, contracts: [...]}).
     */
    public function store(Request $request)
    {
        if ($request->input('action') === 'bulk_register' || is_array($request->input('contracts'))) {
            return $this->bulkRegister($request);
        }

        $result = $this->registerOne($request->all());

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Register many contracts in one request. Response shape matches what the
     * exporter expects: { successful, failed, errors[] }.
     */
    public function bulkRegister(Request $request)
    {
        $rows = $request->input('contracts', []);

        if (! is_array($rows) || empty($rows)) {
            return response()->json([
                'success' => false,
                'error_code' => 'EMPTY_BATCH',
                'message' => 'No contracts provided in the bulk payload.',
            ], 422);
        }

        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $row = is_array($row) ? $row : [];
            $result = $this->registerOne($row);

            if (! empty($result['body']['success'])) {
                $successful++;

                continue;
            }

            $body = $result['body'];
            $failed++;
            $errors[] = [
                'index' => $index,
                'contract_id' => $row['contract_id'] ?? null,
                'employee_no' => $row['employee_no'] ?? null,
                'employee_name' => $row['employee_name'] ?? null,
                'error' => $body['error_summary'] ?? $body['message'] ?? 'Unknown error',
                'error_code' => $body['error_code'] ?? null,
                'http_code' => $result['status'],
                'detailed_errors' => $body['detailed_errors'] ?? null,
            ];
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => 'Bulk contract registration completed',
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($rows),
            'errors' => $errors,
        ], 200);
    }

    /**
     * Register/update a single contract from a flat data array.
     */
    private function registerOne(array $data): array
    {
        try {
            $data = $this->normalizeEmptyStringsToNull($data);

            $data['start_date'] = $this->normalizeDate($data['start_date'] ?? ($data['joined_date'] ?? null));
            $data['expire_date'] = $this->normalizeDate($data['expire_date'] ?? ($data['end_date'] ?? null));

            $validator = Validator::make($data, [
                'employee_no' => 'required|string|max:50',
                'contract_type' => 'nullable|string',
                'start_date' => 'required|date',
                'expire_date' => 'nullable|date|after_or_equal:start_date',
                'department' => 'nullable|string',
                'title' => 'nullable|string',
                'contract_status' => 'nullable|string',
                'contract_attachment' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0',
                'payment_frequency' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::error('Contract Registration Validation Failed', [
                    'contract_id' => $data['contract_id'] ?? 'N/A',
                    'employee_no' => $data['employee_no'] ?? 'N/A',
                    'errors' => $validator->errors(),
                ]);

                return ['status' => 422, 'body' => $this->validationErrorPayload($validator, 'Contract registration validation failed', [
                    'contract_id' => $data['contract_id'] ?? null,
                    'employee_no' => $data['employee_no'] ?? null,
                    'employee_name' => $data['employee_name'] ?? null,
                ])];
            }

            $employee = Employee::where('employee_no', $data['employee_no'])->first();
            if (! $employee) {
                return ['status' => 404, 'body' => [
                    'success' => false,
                    'error_code' => 'EMPLOYEE_NOT_FOUND',
                    'message' => 'Employee not found',
                    'error_summary' => "No employee matches employee_no '{$data['employee_no']}'. Register the employee first.",
                    'contract_id' => $data['contract_id'] ?? null,
                    'employee_no' => $data['employee_no'] ?? null,
                    'employee_name' => $data['employee_name'] ?? null,
                ]];
            }

            DB::beginTransaction();

            $firstUserId = DB::table('users')->first()->id ?? null;
            $addedBy = $employee->added_by ?? $firstUserId;

            $departmentId = ! empty($data['department'])
                ? $this->resolveOrCreateDepartment($data['department'], $addedBy)
                : $employee->department_id;

            $positionId = $this->resolveOrCreateJobTitle($data['title'] ?? null, $addedBy)
                ?? $employee->title_id
                ?? DB::table('jobtitles')->first()->id ?? null;

            $workstationId = $employee->workstation_id ?? (DB::table('workstations')->first()->id ?? null);

            if (! $positionId || ! $workstationId) {
                DB::rollBack();

                return ['status' => 422, 'body' => [
                    'success' => false,
                    'error_code' => 'FIELD_RESOLUTION_FAILED',
                    'message' => 'Could not resolve required organizational fields',
                    'error_summary' => 'Missing position or workstation reference for this contract.',
                    'contract_id' => $data['contract_id'] ?? null,
                    'employee_no' => $data['employee_no'] ?? null,
                ]];
            }

            $contractType = $this->normalizeContractType($data['contract_type'] ?? null);
            $status = $this->normalizeStatus($data['contract_status'] ?? null, $data['expire_date'] ?? null);

            $attributes = [
                'employee_id' => $employee->id,
                'workstation_id' => $workstationId,
                'department_id' => $departmentId,
                'position_id' => $positionId,
                'contract_type' => $contractType,
                'status' => $status,
                'start_date' => $data['start_date'],
                'expire_date' => $data['expire_date'] ?: $data['start_date'],
                'expirenotification' => false,
                'payment_frequency' => $data['payment_frequency'] ?? 'monthly',
                'base_salary' => $data['base_salary'] ?? 0,
                'attachment' => $data['contract_attachment'] ?? '',
                'description' => $data['description'] ?? null,
                'added_by' => $addedBy,
            ];

            // A contract is matched (and updated) when employee + start_date
            // line up. Otherwise a new row is created. This keeps re-runs
            // of the exporter idempotent.
            $existing = Employeecontracts::where('employee_id', $employee->id)
                ->whereDate('start_date', $attributes['start_date'])
                ->first();

            if ($existing) {
                $existing->update($attributes);
                $contract = $existing;
                $httpStatus = 200;
                $message = 'Contract updated successfully';
            } else {
                $contract = Employeecontracts::create($attributes);
                $httpStatus = 201;
                $message = 'Contract registered successfully';
            }

            DB::commit();

            return ['status' => $httpStatus, 'body' => [
                'success' => true,
                'message' => $message,
                'data' => [
                    'contract' => $contract->load(['employee', 'department', 'position', 'workstation']),
                ],
            ]];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Contract Registration Failed', [
                'contract_id' => $data['contract_id'] ?? 'N/A',
                'employee_no' => $data['employee_no'] ?? 'N/A',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['status' => 500, 'body' => [
                'success' => false,
                'error_code' => 'REGISTRATION_FAILED',
                'message' => 'Failed to register contract due to server error',
                'error_summary' => $e->getMessage(),
                'contract_id' => $data['contract_id'] ?? null,
                'employee_no' => $data['employee_no'] ?? null,
                'employee_name' => $data['employee_name'] ?? null,
            ]];
        }
    }

    /**
     * Map the remote system's contract_type string onto the local enum.
     * Anything unrecognised becomes "other".
     */
    private function normalizeContractType(?string $value): string
    {
        $allowed = ['permanent', 'temporary', 'part_time', 'probation', 'internship', 'consultancy', 'other'];

        if (empty($value)) {
            return 'permanent';
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', trim($value)));

        return in_array($normalized, $allowed, true) ? $normalized : 'other';
    }

    /**
     * Map the remote system's status string onto the local enum. If status is
     * absent, derive it from the expire date.
     */
    private function normalizeStatus(?string $value, ?string $expireDate): string
    {
        $allowed = ['active', 'expired', 'suspended', 'terminated'];
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, $allowed, true)) {
            return $normalized;
        }

        if ($expireDate && strtotime($expireDate) !== false && strtotime($expireDate) < time()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Resolve or create job title (kept local since EmployeeController's copy
     * is private). Same fallbacks: name match, then auto-create, then random.
     */
    private function resolveOrCreateJobTitle(?string $name, ?string $addedBy): ?string
    {
        if (empty($name)) {
            return null;
        }

        $existing = DB::table('jobtitles')
            ->where('name', 'LIKE', '%'.$name.'%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        try {
            $id = (string) \Illuminate\Support\Str::uuid();
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
            Log::warning("Failed to create job title '{$name}': {$e->getMessage()}");

            return DB::table('jobtitles')->first()->id ?? null;
        }
    }

    /**
     * List contracts (paginated).
     */
    public function index()
    {
        try {
            $contracts = Employeecontracts::with(['employee', 'department', 'position', 'workstation'])
                ->latest()
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $contracts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contracts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single contract by id.
     */
    public function show(string $id)
    {
        try {
            $contract = Employeecontracts::with(['employee', 'department', 'position', 'workstation'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contract,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}

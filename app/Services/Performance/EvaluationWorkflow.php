<?php

namespace App\Services\Performance;

use App\Models\approvallevel;
use App\Models\approvalleveltoemployee;
use App\Models\Employee;
use App\Models\EmployeePlanImplementation;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Who acts next on a performance evaluation, and who may verify the work behind it.
 *
 * Levels come from approval levels mapped to the Performance document. An approver's mapping
 * can be limited to departments; a mapping with no departments applies company-wide. A level
 * only counts for an employee if someone other than that employee can act on it, so levels
 * with no eligible approver are skipped rather than leaving the evaluation stuck.
 */
class EvaluationWorkflow
{
    public const OPEN_STATUSES = ['submitted', 'under_review'];

    /**
     * @return array<int, int> Level orders that can act on this employee's evaluation, lowest first
     */
    public static function levelOrdersFor(Employee $employee): array
    {
        return static::ordersFrom(static::approverMappings(), $employee);
    }

    public static function firstLevelOrder(Employee $employee): ?int
    {
        return static::levelOrdersFor($employee)[0] ?? null;
    }

    public static function nextLevelOrder(Employee $employee, int $currentOrder): ?int
    {
        return collect(static::levelOrdersFor($employee))->first(fn ($order) => $order > $currentOrder);
    }

    /**
     * The level at which this user may act on the evaluation right now, if any.
     */
    public static function approvalLevelFor(User $user, PerformanceEvaluation $evaluation): ?approvallevel
    {
        $employee = $evaluation->employee;

        if (! $employee || ! in_array($evaluation->status, self::OPEN_STATUSES, true)) {
            return null;
        }

        return static::mappingsOf($user)
            ->filter(fn ($mapping) => static::covers($mapping, $employee)
                && (int) $mapping->approval_level->level_order === (int) $evaluation->current_approval_level)
            ->first()?->approval_level;
    }

    public static function canApprove(User $user, PerformanceEvaluation $evaluation): bool
    {
        return static::approvalLevelFor($user, $evaluation) !== null;
    }

    /**
     * Evaluations waiting for this user, oldest first.
     */
    public static function pendingFor(User $user): Collection
    {
        $mappings = static::mappingsOf($user);

        if ($mappings->isEmpty()) {
            return collect();
        }

        return PerformanceEvaluation::whereIn('status', self::OPEN_STATUSES)
            ->whereIn('current_approval_level', $mappings->map(fn ($mapping) => $mapping->approval_level->level_order)->unique())
            ->with(['employee.department', 'employeePlan.employeePlanItems'])
            ->orderBy('submitted_at')
            ->get()
            ->filter(fn (PerformanceEvaluation $evaluation) => $evaluation->employee && $mappings->contains(
                fn ($mapping) => static::covers($mapping, $evaluation->employee)
                    && (int) $mapping->approval_level->level_order === (int) $evaluation->current_approval_level
            ))
            ->values();
    }

    /**
     * Departments this user approves for. Null means all departments.
     *
     * @return array<int, string>|null
     */
    public static function coveredDepartmentIds(User $user): ?array
    {
        $mappings = static::mappingsOf($user);

        if ($mappings->contains(fn ($mapping) => $mapping->departments->isEmpty())) {
            return null;
        }

        return $mappings->flatMap(fn ($mapping) => $mapping->departments->pluck('id'))->unique()->values()->all();
    }

    /**
     * Moves open evaluations off levels nobody can act on: those submitted before approvals
     * were configured, or left behind when the setup changed. Safe to run repeatedly.
     *
     * @return int Number of evaluations moved
     */
    public static function releaseStranded(): int
    {
        $mappings = static::approverMappings();
        $moved = 0;

        $open = PerformanceEvaluation::whereIn('status', self::OPEN_STATUSES)->with('employee')->get();

        foreach ($open as $evaluation) {
            if (! $evaluation->employee) {
                continue;
            }

            $orders = static::ordersFrom($mappings, $evaluation->employee);

            if ($orders === [] || in_array((int) $evaluation->current_approval_level, $orders, true)) {
                continue;
            }

            $evaluation->update([
                'current_approval_level' => collect($orders)->first(fn ($order) => $order >= $evaluation->current_approval_level)
                    ?? end($orders),
            ]);
            $moved++;
        }

        return $moved;
    }

    /**
     * Whether this user has anyone's implementation entries to verify.
     */
    public static function canVerifyAny(User $user): bool
    {
        return EmployeePlanAccess::canManage($user) || static::mappingsOf($user)->isNotEmpty();
    }

    /**
     * Entries this user may verify: approvers for the departments they cover, and employee plan
     * managers for the plans they can see. Never the user's own entries.
     */
    public static function verifiableEntries(User $user): Builder
    {
        $ownEmployeeId = Employee::where('user_id', $user->id)->value('id');
        $canManage = EmployeePlanAccess::canManage($user);
        $isApprover = static::mappingsOf($user)->isNotEmpty();
        $coveredDepartmentIds = $isApprover ? static::coveredDepartmentIds($user) : [];

        return EmployeePlanImplementation::query()
            ->when($ownEmployeeId, fn (Builder $q) => $q->where('employee_id', '!=', $ownEmployeeId))
            ->where(function (Builder $q) use ($user, $canManage, $isApprover, $coveredDepartmentIds) {
                $q->whereRaw('1 = 0');

                if ($isApprover) {
                    $q->orWhereHas('employee', fn (Builder $employee) => $employee->when(
                        $coveredDepartmentIds !== null,
                        fn (Builder $e) => $e->whereIn('department_id', $coveredDepartmentIds)
                    ));
                }

                if ($canManage) {
                    $q->orWhereHas('employeePlanItem', fn (Builder $item) => $item->whereIn(
                        'employee_plan_id',
                        EmployeePlanAccess::visiblePlans($user)->select('id')
                    ));
                }
            });
    }

    public static function canVerify(User $user, EmployeePlanImplementation $entry): bool
    {
        return static::verifiableEntries($user)->whereKey($entry->id)->exists();
    }

    /**
     * Active mappings on Performance levels whose approver is an active employee with a login.
     */
    private static function approverMappings(): Collection
    {
        $levelIds = approvallevel::where('is_active', true)
            ->whereNotNull('level_order')
            ->whereHas('approvalleveltodocuments', fn (Builder $q) => $q->where('document_type', 'Performance')->where('is_active', true))
            ->pluck('id');

        return approvalleveltoemployee::where('is_active', true)
            ->whereIn('approval_level_id', $levelIds)
            ->whereHas('employee', fn (Builder $q) => $q->where('status', 'Active')->whereNotNull('user_id'))
            ->with(['approval_level', 'departments:id'])
            ->get();
    }

    private static function mappingsOf(User $user): Collection
    {
        $employeeId = Employee::where('user_id', $user->id)->value('id');

        return $employeeId
            ? static::approverMappings()->where('employee_id', $employeeId)->values()
            : collect();
    }

    /** An approver never acts on their own evaluation. */
    private static function covers(approvalleveltoemployee $mapping, Employee $employee): bool
    {
        return $mapping->employee_id !== $employee->id && $mapping->appliesToDepartment($employee->department_id);
    }

    /**
     * @return array<int, int>
     */
    private static function ordersFrom(Collection $mappings, Employee $employee): array
    {
        return $mappings
            ->filter(fn ($mapping) => static::covers($mapping, $employee))
            ->map(fn ($mapping) => (int) $mapping->approval_level->level_order)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}

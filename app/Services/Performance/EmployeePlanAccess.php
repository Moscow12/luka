<?php

namespace App\Services\Performance;

use App\Models\EmployeePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central answer to "which employee plans may this user see and change?".
 * Follows the same department rule as DepartmentPlanAccess, including its view-all permission.
 */
class EmployeePlanAccess
{
    public static function canManage(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage-employee-plans');
    }

    /**
     * A plan belongs to the department of its department plan. Plans with no department plan
     * belong to the employee's department. Users with no department match nothing.
     */
    public static function visiblePlans(User $user): Builder
    {
        return EmployeePlan::query()->when(! DepartmentPlanAccess::canViewAll($user), function (Builder $query) use ($user) {
            $departmentIds = array_filter([DepartmentPlanAccess::ownDepartmentId($user)]);

            $query->where(function (Builder $q) use ($departmentIds) {
                $q->whereHas('departmentPlan', fn (Builder $plan) => $plan->whereIn('department_id', $departmentIds))
                    ->orWhere(function (Builder $q) use ($departmentIds) {
                        $q->whereDoesntHave('departmentPlan')
                            ->whereHas('employee', fn (Builder $employee) => $employee->whereIn('department_id', $departmentIds));
                    });
            });
        });
    }
}

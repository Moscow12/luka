<?php

namespace App\Services\Performance;

use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central answer to "whose assigned duties may this user see and change?".
 * View-all holders see everyone, duty managers their department, other staff only themselves.
 * A duty belongs to its employee's department. Users with no employee record match nothing.
 */
class AssignedDutyAccess
{
    public static function canViewAll(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view-all-duties');
    }

    public static function canManage(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('assign-duties');
    }

    public static function visibleEmployees(User $user): Builder
    {
        return static::limitEmployees(Employee::query(), $user);
    }

    public static function visibleDuties(User $user): Builder
    {
        return EmployeeAssignedDuty::query()->when(
            ! static::canViewAll($user),
            fn (Builder $q) => $q->whereHas('employee', fn (Builder $employee) => static::limitEmployees($employee, $user))
        );
    }

    private static function limitEmployees(Builder $employees, User $user): Builder
    {
        if (static::canViewAll($user)) {
            return $employees;
        }

        return static::canManage($user)
            ? $employees->whereIn('department_id', array_filter([DepartmentPlanAccess::ownDepartmentId($user)]))
            : $employees->where('user_id', $user->id);
    }
}

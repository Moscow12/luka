<?php

namespace App\Services\Performance;

use App\Models\DepartmentPlan;
use App\Models\departments;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central answer to "which department plans may this user see and change?".
 * Viewing and managing are separate: seeing every plan does not grant editing them,
 * and managing plans stays limited to the plans the user can see.
 */
class DepartmentPlanAccess
{
    public static function canViewAll(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view-all-dept-plans');
    }

    public static function canManage(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage-dept-plans');
    }

    public static function ownDepartmentId(User $user): ?string
    {
        return Employee::where('user_id', $user->id)->value('department_id');
    }

    public static function visiblePlans(User $user): Builder
    {
        return static::limitToOwnDepartment(DepartmentPlan::query(), 'department_id', $user);
    }

    public static function visibleDepartments(User $user): Builder
    {
        return static::limitToOwnDepartment(departments::query(), 'id', $user);
    }

    /** Users with no department match nothing. */
    private static function limitToOwnDepartment(Builder $query, string $column, User $user): Builder
    {
        return $query->when(
            ! static::canViewAll($user),
            fn (Builder $q) => $q->whereIn($column, array_filter([static::ownDepartmentId($user)]))
        );
    }
}

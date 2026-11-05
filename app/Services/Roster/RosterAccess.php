<?php

namespace App\Services\Roster;

use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeroster;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central answer to "whose rosters may this user see and change?".
 * Everyone is limited to their own department unless they hold view-all-rosters.
 * Users with no department match nothing.
 */
class RosterAccess
{
    public static function canViewAll(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view-all-rosters');
    }

    public static function canView(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->canAny(['view-roster', 'view-all-rosters', 'create-roster', 'edit-roster', 'manage-roster', 'approve-roster']);
    }

    public static function canCreate(User $user): bool
    {
        return $user->isSuperAdmin() || $user->canAny(['create-roster', 'manage-roster']);
    }

    public static function canEdit(User $user): bool
    {
        return $user->isSuperAdmin() || $user->canAny(['edit-roster', 'manage-roster']);
    }

    public static function ownDepartmentId(User $user): ?string
    {
        return Employee::where('user_id', $user->id)->value('department_id');
    }

    /** The department a page starts on, honouring a requested one only for view-all holders. */
    public static function initialDepartment(User $user, ?string $requested): string
    {
        return (static::canViewAll($user) && $requested) ? $requested : (static::ownDepartmentId($user) ?? '');
    }

    public static function visibleDepartments(User $user): Builder
    {
        return static::limit(departments::query(), 'id', $user);
    }

    public static function visibleEmployees(User $user): Builder
    {
        return static::limit(Employee::query(), 'department_id', $user);
    }

    public static function visibleRosters(User $user): Builder
    {
        return static::limit(employeeroster::query(), 'department_id', $user);
    }

    private static function limit(Builder $query, string $column, User $user): Builder
    {
        return $query->when(
            ! static::canViewAll($user),
            fn (Builder $q) => $q->whereIn($column, array_filter([static::ownDepartmentId($user)]))
        );
    }
}

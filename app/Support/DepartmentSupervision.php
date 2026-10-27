<?php

namespace App\Support;

use App\Models\departments;
use App\Models\Employee;
use App\Models\User;

/**
 * Who supervises which department: staff of the department holding manage-department-tickets,
 * plus anyone whose job title is the department's supervisor title. Used by tickets and chat.
 */
class DepartmentSupervision
{
    /**
     * @return array<int, string>
     */
    public static function departmentIdsFor(User $user): array
    {
        $employee = Employee::where('user_id', $user->id)->first();
        if (! $employee) {
            return [];
        }

        $ids = [];
        if ($user->can('manage-department-tickets') && $employee->department_id) {
            $ids[] = $employee->department_id;
        }

        if ($employee->title_id) {
            $ids = array_merge($ids, departments::where('supervisor_title_id', $employee->title_id)->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }

    /**
     * User IDs of everyone supervising the department.
     *
     * @return array<int, string>
     */
    public static function supervisorUserIds(departments $department): array
    {
        $byTitle = $department->supervisor_title_id
            ? Employee::where('title_id', $department->supervisor_title_id)->whereNotNull('user_id')->pluck('user_id')->all()
            : [];

        $byPermission = Employee::where('department_id', $department->id)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->filter(fn ($userId) => User::find($userId)?->can('manage-department-tickets'))
            ->all();

        return array_values(array_unique(array_merge($byTitle, $byPermission)));
    }
}

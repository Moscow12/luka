<?php

namespace App\Services\HrDocuments;

use App\Models\Employee;
use App\Models\User;
use App\Services\Leave\LeaveApprovalChain;
use App\Support\DepartmentSupervision;
use Illuminate\Database\Eloquent\Model;

/** Who may open a sensitive HR file. */
class HrDocumentAccess
{
    public static function canView(User $user, HrDocumentType $type, Model $record): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return match ($type) {
            HrDocumentType::InstitutionalContract => $user->canAny(['view-contracts', 'manage-contracts']),

            HrDocumentType::ContractRequest,
            HrDocumentType::ContractRequestLetter => self::isOwner($user, $record->employee_id)
                || $user->canAny(['approve-contract-requests', 'manage-staff']),

            HrDocumentType::Leave => self::canViewEmployeeRecords($user, $record->employee_id)
                || $user->can('manage-leave')
                || self::isLeaveApproverFor($user, $record->employee_id),

            default => self::canViewEmployeeRecords($user, $record->employee_id),
        };
    }

    /** The employee themself, HR staff, or a supervisor of the employee's department. */
    public static function canViewEmployeeRecords(User $user, ?string $employeeId): bool
    {
        if (! $employeeId) {
            return false;
        }

        if (self::isOwner($user, $employeeId) || $user->canAny(['view-staff', 'manage-staff'])) {
            return true;
        }

        $departmentId = Employee::whereKey($employeeId)->value('department_id');

        return $departmentId && in_array($departmentId, DepartmentSupervision::departmentIdsFor($user), true);
    }

    protected static function isOwner(User $user, ?string $employeeId): bool
    {
        return $employeeId && Employee::whereKey($employeeId)->where('user_id', $user->id)->exists();
    }

    /** Mapped as a leave approver for the requester's department (or for all departments). */
    protected static function isLeaveApproverFor(User $user, ?string $employeeId): bool
    {
        $departmentId = $employeeId ? Employee::whereKey($employeeId)->value('department_id') : null;

        foreach (app(LeaveApprovalChain::class)->mappingsFor($user) as $mapping) {
            if (empty($mapping['department_ids']) || in_array($departmentId, $mapping['department_ids'], true)) {
                return true;
            }
        }

        return false;
    }
}

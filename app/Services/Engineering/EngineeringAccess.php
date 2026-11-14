<?php

namespace App\Services\Engineering;

use App\Models\Employee;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Central answer to "what may this user do in engineering?".
 *
 * Roles come from two places: permissions (helpdesk, HOD, store, certifier, ...) and
 * section membership (engineer; Head of Section when is_head is set).
 */
class EngineeringAccess
{
    public static function employee(User $user): ?Employee
    {
        return Employee::where('user_id', $user->id)->first();
    }

    /** HOD / engineering manager: everything in engineering. */
    public static function isManager(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-manage');
    }

    public static function isHelpdesk(User $user): bool
    {
        return static::isManager($user) || $user->can('engineering-helpdesk');
    }

    /**
     * @return array<int, string>
     */
    public static function headedSectionIds(User $user): array
    {
        return static::sectionIds($user, headOnly: true);
    }

    /**
     * @return array<int, string>
     */
    public static function memberSectionIds(User $user): array
    {
        return static::sectionIds($user, headOnly: false);
    }

    public static function isEngineer(User $user): bool
    {
        return static::memberSectionIds($user) !== [];
    }

    public static function isHeadOfSection(User $user): bool
    {
        return static::headedSectionIds($user) !== [];
    }

    /** Helpdesk and HOD assign anywhere; a Head of Section only within their own sections. */
    public static function canAssign(User $user, ?string $sectionId = null): bool
    {
        if (static::isHelpdesk($user)) {
            return true;
        }

        $headed = static::headedSectionIds($user);

        return $sectionId === null ? $headed !== [] : in_array($sectionId, $headed, true);
    }

    /** Null means every section. */
    public static function supervisedSectionIds(User $user): ?array
    {
        return static::isHelpdesk($user) || $user->can('engineering-reports')
            ? null
            : static::headedSectionIds($user);
    }

    /**
     * Sections the user may pick on workload, assignment and report screens.
     */
    public static function selectableSections(User $user)
    {
        $ids = static::supervisedSectionIds($user);

        return Section::active()->when($ids !== null, fn (Builder $q) => $q->whereIn('id', $ids))->get();
    }

    /**
     * Row-level scope: all jobs for helpdesk/HOD/management, a section's jobs for its head,
     * and otherwise only jobs the user is the engineer or a named assistant on.
     */
    public static function scopeVisibleJobs(Builder $query, User $user): Builder
    {
        if (static::isHelpdesk($user) || $user->can('engineering-reports')) {
            return $query;
        }

        $employeeId = static::employee($user)?->id;
        $headed = static::headedSectionIds($user);

        return $query->where(function (Builder $q) use ($employeeId, $headed) {
            $q->whereIn('eng_jobs.section_id', $headed);

            if ($employeeId) {
                $q->orWhere('eng_jobs.engineer_id', $employeeId)
                    ->orWhereHas('assistants', fn (Builder $a) => $a->where('employees.id', $employeeId));
            }
        });
    }

    /** Jobs the user works on personally (engineer or named assistant). */
    public static function scopeOwnJobs(Builder $query, User $user): Builder
    {
        $employeeId = static::employee($user)?->id;

        if (! $employeeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(fn (Builder $q) => $q
            ->where('eng_jobs.engineer_id', $employeeId)
            ->orWhereHas('assistants', fn (Builder $a) => $a->where('employees.id', $employeeId)));
    }

    public static function isOnJob(User $user, MaintenanceJob $job): bool
    {
        $employeeId = static::employee($user)?->id;

        return $employeeId !== null
            && ($job->engineer_id === $employeeId || $job->assistants()->where('employees.id', $employeeId)->exists());
    }

    public static function isRequester(User $user, MaintenanceJob $job): bool
    {
        return $job->requisition->requested_by === $user->id;
    }

    /** Engineering-side view of a job (workspace, job card, RCA). */
    public static function canViewJob(User $user, MaintenanceJob $job): bool
    {
        return static::isHelpdesk($user)
            || $user->can('engineering-reports')
            || in_array($job->section_id, static::headedSectionIds($user), true)
            || static::isOnJob($user, $job);
    }

    /** Only the people on the job change it, and only while it is open. */
    public static function canWorkJob(User $user, MaintenanceJob $job): bool
    {
        return $job->isOpen() && static::isOnJob($user, $job);
    }

    /** The read-only one-page summary is also open to the requester and the people who process job cards. */
    public static function canViewSummary(User $user, MaintenanceJob $job): bool
    {
        return static::canViewJob($user, $job)
            || static::isRequester($user, $job)
            || static::canProcessJobCards($user)
            || static::canRunStore($user);
    }

    public static function canManageInventory(User $user): bool
    {
        return static::isManager($user) || $user->can('engineering-inventory') || static::isHeadOfSection($user);
    }

    public static function canRunStore(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-store');
    }

    public static function canCertify(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-certify');
    }

    public static function canAuthorise(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-authorise');
    }

    public static function canPurchase(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-purchasing');
    }

    public static function canProcessJobCards(User $user): bool
    {
        return static::canCertify($user) || static::canAuthorise($user) || static::canPurchase($user);
    }

    /** Reports: management, helpdesk and heads of section; an engineer sees only their own figures. */
    public static function canViewReports(User $user): bool
    {
        return static::isHelpdesk($user) || $user->can('engineering-reports') || static::isHeadOfSection($user);
    }

    public static function canSetup(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('engineering-setup');
    }

    /** Anyone with a role on the engineering side (as opposed to a requester only). */
    public static function isEngineeringStaff(User $user): bool
    {
        return static::canViewReports($user) || static::isEngineer($user);
    }

    /**
     * @return array<int, string>
     */
    protected static function sectionIds(User $user, bool $headOnly): array
    {
        return DB::table('eng_section_staff')
            ->join('employees', 'employees.id', '=', 'eng_section_staff.employee_id')
            ->where('employees.user_id', $user->id)
            ->when($headOnly, fn ($q) => $q->where('eng_section_staff.is_head', true))
            ->pluck('eng_section_staff.section_id')
            ->all();
    }
}

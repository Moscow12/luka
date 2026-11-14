<?php

namespace App\Services\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Models\Engineering\JobCard;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\PpmSchedule;
use App\Models\Engineering\SparesRequisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * What the Engineering sidebar menu shows a user: which items they may open, and the
 * counts behind the alert badges.
 */
class EngineeringNavigation
{
    /**
     * @return array{can: array<string, bool>, badges: array<string, int>, total: int}
     */
    public static function for(User $user): array
    {
        // The sidebar is rendered twice per page (desktop and mobile): build the menu once.
        // Kept in the container so it lives exactly as long as the request.
        $key = 'engineering.navigation.'.$user->id;

        if (! app()->bound($key)) {
            app()->instance($key, static::build($user));
        }

        return app($key);
    }

    /** False until the module's migrations have been run, so the rest of the app keeps working. */
    public static function installed(): bool
    {
        if (! app()->bound('engineering.installed')) {
            app()->instance('engineering.installed', Schema::hasTable('eng_sections'));
        }

        return app('engineering.installed');
    }

    /**
     * @return array{can: array<string, bool>, badges: array<string, int>, total: int}
     */
    protected static function build(User $user): array
    {
        $isStaff = EngineeringAccess::isEngineeringStaff($user);
        $processesCards = EngineeringAccess::canProcessJobCards($user);

        $can = [
            'assign' => EngineeringAccess::canAssign($user),
            'workload' => EngineeringAccess::canViewReports($user),
            'jobs' => EngineeringAccess::isEngineer($user),
            'store' => EngineeringAccess::canRunStore($user) || $processesCards || EngineeringAccess::canViewReports($user),
            'reports' => $isStaff,
            'management_reports' => EngineeringAccess::canViewReports($user),
            'procurement' => $isStaff || $processesCards || EngineeringAccess::canRunStore($user),
            'setup' => EngineeringAccess::canSetup($user),
        ];

        $badges = [
            // Finished work the user still has to rate
            'feedback' => MaintenanceJob::awaitingFeedback()
                ->whereHas('requisition', fn (Builder $q) => $q->where('requested_by', $user->id))->count(),
            'queue' => $can['assign'] ? static::waitingJobs($user) : 0,
            'jobs' => $can['jobs'] ? EngineeringAccess::scopeOwnJobs(MaintenanceJob::query(), $user)
                ->whereIn('status', [JobStatus::Assigned->value, JobStatus::Rework->value])->count() : 0,
            'ppm' => $isStaff ? PpmSchedule::active()->needingAttention()->count() : 0,
            'store' => static::storeWork($user),
        ];

        return ['can' => $can, 'badges' => $badges, 'total' => array_sum($badges)];
    }

    /** Submitted jobs waiting for assignment in the sections this user assigns for. */
    protected static function waitingJobs(User $user): int
    {
        $sectionIds = EngineeringAccess::supervisedSectionIds($user);

        return MaintenanceJob::where('status', JobStatus::Submitted->value)
            ->when($sectionIds !== null, fn (Builder $q) => $q->whereIn('section_id', $sectionIds))
            ->count();
    }

    /** Spares for the store keeper, plus job cards waiting on this user's stage. */
    protected static function storeWork(User $user): int
    {
        $stages = array_keys(array_filter([
            'pending_certification' => EngineeringAccess::canCertify($user),
            'pending_authorisation' => EngineeringAccess::canAuthorise($user),
            'approved' => EngineeringAccess::canPurchase($user),
        ]));

        return (EngineeringAccess::canRunStore($user) ? SparesRequisition::whereIn('status', ['requested', 'partially_issued'])->count() : 0)
            + ($stages === [] ? 0 : JobCard::whereIn('status', $stages)->count());
    }
}

<?php

namespace App\Services\Performance;

use App\Models\DepartmentPlan;
use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanImplementation;
use App\Models\EmployeePlanItem;
use App\Models\OrganizationalPlan;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One organisational plan seen from the top down: organisation goals, the department plans that
 * deliver them, the employee plans under each department, and the verified work and evaluations
 * behind every employee goal.
 *
 * Everything is loaded with a fixed number of queries and assembled bottom-up (employee ->
 * department -> organisation), so each level's numbers are built from the level below.
 * Department plans are limited to what DepartmentPlanAccess lets the user see.
 */
class PerformanceDashboard
{
    /** Days without logged work after which an employee plan is flagged as inactive. */
    public const INACTIVE_AFTER_DAYS = 30;

    public const EVALUATION_STAGES = [
        'not_started' => ['label' => 'Not started', 'color' => 'secondary'],
        'draft' => ['label' => 'Draft', 'color' => 'secondary'],
        'in_review' => ['label' => 'In review', 'color' => 'info'],
        'approved' => ['label' => 'Approved', 'color' => 'success'],
        'rejected' => ['label' => 'Returned', 'color' => 'danger'],
    ];

    public readonly float $expected;

    /** @var array<string, array> Employee plan rows keyed by employee plan id */
    private array $employeePlans = [];

    /** @var array<string, array> Department rows keyed by department plan id */
    private array $departmentPlans = [];

    /** @var array<string, array> Departments with no plan under this organisational plan */
    private array $unplannedDepartments = [];

    private array $organisationGoals = [];

    /** @var Collection<int, \App\Models\OrganizationalPlanItem> All goals of the plan, inactive ones included for naming */
    private Collection $organisationItems;

    public function __construct(
        public readonly OrganizationalPlan $plan,
        private readonly User $user,
        ?CarbonInterface $today = null,
    ) {
        $this->expected = PlanProgress::expected($plan->start_date, $plan->end_date, $today ?? Carbon::today());
        $this->build($today ?? Carbon::today());
    }

    public static function canView(User $user): bool
    {
        return $user->isSuperAdmin() || $user->canAny([
            'view-performance', 'manage-org-plans', 'view-all-dept-plans', 'manage-dept-plans', 'manage-employee-plans',
        ]);
    }

    /** Organisation-wide figures are only shown to users who can see every department. */
    public static function canViewOrganisation(User $user): bool
    {
        return DepartmentPlanAccess::canViewAll($user);
    }

    /** Plans worth reporting on, newest first. */
    public static function selectablePlans(): Collection
    {
        return OrganizationalPlan::where('status', '!=', 'cancelled')
            ->orderByDesc('start_date')
            ->get(['id', 'plan_name', 'start_date', 'end_date', 'status']);
    }

    /** The active plan covering today, else the latest active plan, else the latest plan. */
    public static function defaultPlan(Collection $plans): ?OrganizationalPlan
    {
        $today = Carbon::today();

        return $plans->first(fn ($p) => $p->status === 'active' && $p->start_date?->lte($today) && $p->end_date?->gte($today))
            ?? $plans->firstWhere('status', 'active')
            ?? $plans->first();
    }

    // ---------------------------------------------------------------------------------------------
    // Read side used by the dashboard
    // ---------------------------------------------------------------------------------------------

    public function organisation(): array
    {
        $departments = collect($this->departmentPlans);
        $employees = collect($this->employeePlans);
        $goals = collect($this->organisationGoals);

        $progress = PlanProgress::weighted($goals->map(fn ($g) => ['progress' => $g['progress'] ?? 0, 'weight' => $g['weight']]));
        $headcount = $departments->sum('headcount') + collect($this->unplannedDepartments)->sum('headcount');

        return [
            'progress' => $progress,
            'status' => PlanProgress::status($progress, $this->expected),
            'goals' => $goals->values()->all(),
            'goals_cascaded' => $goals->where('departments', '>', 0)->count(),
            'departments' => $departments->sortBy('progress')->values()->all(),
            'unplanned_departments' => array_values($this->unplannedDepartments),
            'department_total' => $departments->count() + count($this->unplannedDepartments),
            'headcount' => $headcount,
            'staff_planned' => $employees->pluck('employee_id')->unique()->count(),
            'activity' => $this->activityTotals($employees),
            'evaluations' => $this->evaluationSummary($employees),
            'smart' => $this->smartSummary($departments->flatMap(fn ($d) => $d['goals'])->merge($employees->flatMap(fn ($e) => $e['goals']))),
            'issues' => $this->issues($goals, $departments, $employees),
        ];
    }

    public function department(string $departmentPlanId): ?array
    {
        $department = $this->departmentPlans[$departmentPlanId] ?? null;

        if (! $department) {
            return null;
        }

        $employees = collect($this->employeePlans)->where('department_plan_id', $departmentPlanId);

        return $department + [
            'employees' => $employees->sortBy('progress')->values()->all(),
            'evaluations' => $this->evaluationSummary($employees),
            'activity' => $this->activityTotals($employees),
            'smart' => $this->smartSummary(collect($department['goals'])->merge($employees->flatMap(fn ($e) => $e['goals']))),
            'issues' => $this->issues(collect(), collect([$department]), $employees),
        ];
    }

    public function employee(string $employeePlanId): ?array
    {
        $employee = $this->employeePlans[$employeePlanId] ?? null;

        if (! $employee) {
            return null;
        }

        $department = $this->departmentPlans[$employee['department_plan_id']] ?? null;

        return $employee + [
            'department' => $department['department'] ?? null,
            'parent_names' => collect($department['goals'] ?? [])->pluck('name', 'id')->all(),
            'smart' => $this->smartSummary(collect($employee['goals'])),
            'issues' => $this->issues(collect(), collect(), collect([$employee])),
            'recent' => $this->recentActivity($employeePlanId),
        ];
    }

    /** The department plan this user belongs to under the current organisational plan, if any. */
    public function ownDepartmentPlanId(): ?string
    {
        $departmentId = DepartmentPlanAccess::ownDepartmentId($this->user);

        return collect($this->departmentPlans)->firstWhere('department_id', $departmentId)['id'] ?? null;
    }

    public function departmentPlanIds(): array
    {
        return array_keys($this->departmentPlans);
    }

    // ---------------------------------------------------------------------------------------------
    // Building the tree, bottom-up
    // ---------------------------------------------------------------------------------------------

    private function build(CarbonInterface $today): void
    {
        $this->organisationItems = $this->plan->items()->orderBy('display_order')->get();

        $departmentPlans = DepartmentPlanAccess::visiblePlans($this->user)
            ->where('organizational_plan_id', $this->plan->id)
            ->with([
                'department:id,name',
                'departmentPlanItems' => fn ($q) => $q->where('is_active', true)->orderBy('display_order'),
            ])
            ->get();

        $employeePlans = EmployeePlan::whereIn('department_plan_id', $departmentPlans->pluck('id'))
            ->where('status', '!=', 'cancelled')
            ->with([
                'employee:id,first_name,middle_name,last_name,employee_no,department_id',
                'employeePlanItems' => fn ($q) => $q->where('is_active', true)->orderBy('display_order'),
                'performanceEvaluation',
            ])
            ->get();

        $activity = $this->activityByGoal($employeePlans->pluck('id'));
        $headcount = Employee::where('status', 'Active')
            ->whereNotNull('department_id')
            ->selectRaw('department_id, COUNT(*) as total')
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        foreach ($employeePlans as $plan) {
            $this->employeePlans[$plan->id] = $this->employeeRow($plan, $activity, $today);
        }

        foreach ($departmentPlans as $plan) {
            $this->departmentPlans[$plan->id] = $this->departmentRow($plan, (int) ($headcount[$plan->department_id] ?? 0));
        }

        $plannedIds = $departmentPlans->pluck('department_id')->all();
        DepartmentPlanAccess::visibleDepartments($this->user)
            ->whereNotIn('id', $plannedIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->each(function ($department) use ($headcount) {
                $this->unplannedDepartments[$department->id] = [
                    'department_id' => $department->id,
                    'department' => $department->name,
                    'headcount' => (int) ($headcount[$department->id] ?? 0),
                ];
            });

        $this->organisationGoals = $this->organisationRows();
    }

    /**
     * Verified totals and entry counts per employee goal, in one grouped query.
     *
     * @return Collection<string, object>
     */
    private function activityByGoal(Collection $employeePlanIds): Collection
    {
        if ($employeePlanIds->isEmpty()) {
            return collect();
        }

        return EmployeePlanImplementation::query()
            ->whereIn('employee_plan_item_id', EmployeePlanItem::select('id')->whereIn('employee_plan_id', $employeePlanIds))
            ->selectRaw("
                employee_plan_item_id,
                SUM(CASE WHEN status = 'verified' THEN COALESCE(quantity_achieved, 0) ELSE 0 END) AS achieved,
                SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
                AVG(CASE WHEN status = 'verified' THEN verification_score END) AS quality,
                MAX(implementation_date) AS last_activity
            ")
            ->groupBy('employee_plan_item_id')
            ->get()
            ->keyBy('employee_plan_item_id');
    }

    private function employeeRow(EmployeePlan $plan, Collection $activity, CarbonInterface $today): array
    {
        $timeBound = $plan->department_plan_id !== null;

        $goals = $plan->employeePlanItems->map(function (EmployeePlanItem $item) use ($activity, $timeBound) {
            $stats = $activity->get($item->id);
            $target = $item->target_value !== null ? (float) $item->target_value : null;
            $achieved = (float) ($stats->achieved ?? 0);
            $progress = PlanProgress::percent($achieved, $target);

            return $this->goal($item, $progress, $achieved, $item->department_plan_item_id !== null, $timeBound) + [
                'parent_id' => $item->department_plan_item_id,
                'verified' => (int) ($stats->verified ?? 0),
                'pending' => (int) ($stats->pending ?? 0),
                'rejected' => (int) ($stats->rejected ?? 0),
                'quality' => isset($stats->quality) ? round((float) $stats->quality, 1) : null,
                'last_activity' => $stats->last_activity ?? null,
            ];
        });

        $progress = PlanProgress::weighted($goals);
        $lastActivity = $goals->pluck('last_activity')->filter()->max();
        $evaluation = $plan->performanceEvaluation;

        return [
            'id' => $plan->id,
            'plan_name' => $plan->plan_name,
            'plan_status' => $plan->status,
            'department_plan_id' => $plan->department_plan_id,
            'employee_id' => $plan->employee_id,
            'employee_name' => $plan->employee ? Str::squish($plan->employee->getFullName()) : 'Unknown employee',
            'employee_no' => $plan->employee?->employee_no,
            'initials' => strtoupper(substr($plan->employee?->first_name ?? '?', 0, 1).substr($plan->employee?->last_name ?? '', 0, 1)),
            'progress' => $progress,
            'status' => PlanProgress::status($progress, $this->expected),
            'goals' => $goals->all(),
            'weight_total' => round($goals->sum('weight'), 2),
            'verified' => $goals->sum('verified'),
            'pending' => $goals->sum('pending'),
            'rejected' => $goals->sum('rejected'),
            'last_activity' => $lastActivity,
            'inactive' => ! $lastActivity || Carbon::parse($lastActivity)->lt($today->copy()->subDays(self::INACTIVE_AFTER_DAYS)),
            'evaluation' => [
                'stage' => $this->evaluationStage($evaluation?->status),
                'self' => $evaluation?->self_score !== null ? (float) $evaluation->self_score : null,
                'supervisor' => $evaluation?->supervisor_score !== null ? (float) $evaluation->supervisor_score : null,
                'task' => $evaluation?->task_score !== null ? (float) $evaluation->task_score : null,
                'final' => $evaluation?->final_score !== null ? (float) $evaluation->final_score : null,
                'rating' => $evaluation?->status === 'approved' ? PlanProgress::rating((float) $evaluation->final_score) : null,
            ],
        ];
    }

    private function departmentRow(DepartmentPlan $plan, int $headcount): array
    {
        $employees = collect($this->employeePlans)->where('department_plan_id', $plan->id);
        $employeeGoals = $employees->flatMap(fn ($e) => $e['goals'])->groupBy('parent_id');

        $goals = $plan->departmentPlanItems->map(function ($item) use ($employeeGoals) {
            $children = $employeeGoals->get($item->id, collect());

            return $this->cascadedGoal($item, $children, $item->organizational_plan_item_id !== null, true) + [
                'parent_id' => $item->organizational_plan_item_id,
                'parent_name' => $this->organisationItems->firstWhere('id', $item->organizational_plan_item_id)?->item_name,
                'contributors' => $children->count(),
            ];
        });

        $progress = PlanProgress::weighted($goals);

        return [
            'id' => $plan->id,
            'plan_name' => $plan->plan_name,
            'plan_status' => $plan->status,
            'department_id' => $plan->department_id,
            'department' => $plan->department?->name ?? 'Unknown department',
            'progress' => $progress,
            'status' => PlanProgress::status($progress, $this->expected),
            'goals' => $goals->all(),
            'weight_total' => round($goals->sum('weight'), 2),
            'headcount' => $headcount,
            'staff_planned' => $employees->pluck('employee_id')->unique()->count(),
            'pending' => $employees->sum('pending'),
            'verified' => $employees->sum('verified'),
            'evaluated' => $employees->where('evaluation.stage', 'approved')->count(),
            'average_score' => $this->averageFinalScore($employees),
        ];
    }

    private function organisationRows(): array
    {
        $departmentGoals = collect($this->departmentPlans)->flatMap(fn ($d) => $d['goals'])->groupBy('parent_id');

        return $this->organisationItems
            ->where('is_active', true)
            ->map(function ($item) use ($departmentGoals) {
                $children = $departmentGoals->get($item->id, collect());

                return $this->cascadedGoal($item, $children, true, true) + ['departments' => $children->count()];
            })
            ->values()
            ->all();
    }

    /** A goal whose progress comes from the goals that cascade from it. */
    private function cascadedGoal(object $item, Collection $children, bool $linked, bool $timeBound): array
    {
        $target = $item->target_value !== null ? (float) $item->target_value : null;
        $rolled = PlanProgress::rollUp($target, $item->target_unit, $children->all());
        $plannedTarget = $children->sum(fn ($c) => (float) ($c['target'] ?? 0));

        return $this->goal($item, $rolled['progress'], $rolled['achieved'], $linked, $timeBound) + [
            // How much of this goal's target has been handed down; below 100 means part of it is unowned
            'coverage' => PlanProgress::isAdditive($target, $item->target_unit) && $children->isNotEmpty()
                ? round($plannedTarget / $target * 100, 1)
                : null,
            'pending' => $children->sum('pending'),
        ];
    }

    private function goal(object $item, ?float $progress, ?float $achieved, bool $linked, bool $timeBound): array
    {
        $smart = PlanProgress::smart($item, $linked, $timeBound);

        return [
            'id' => $item->id,
            'name' => $item->item_name,
            'unit' => $item->target_unit,
            'target' => $item->target_value !== null ? (float) $item->target_value : null,
            'weight' => (float) $item->weight,
            'achieved' => $achieved,
            'progress' => $progress,
            'status' => PlanProgress::status($progress, $this->expected),
            'smart' => $smart,
            'smart_ok' => ! in_array(false, $smart, true),
        ];
    }

    // ---------------------------------------------------------------------------------------------
    // Summaries
    // ---------------------------------------------------------------------------------------------

    private function evaluationStage(?string $status): string
    {
        return match ($status) {
            null => 'not_started',
            'submitted', 'under_review' => 'in_review',
            'approved', 'rejected', 'draft' => $status,
            default => 'not_started',
        };
    }

    private function evaluationSummary(Collection $employees): array
    {
        $stages = collect(self::EVALUATION_STAGES)->map(fn ($meta, $key) => $meta + [
            'count' => $employees->where('evaluation.stage', $key)->count(),
        ]);

        $approved = $employees->where('evaluation.stage', 'approved');
        $ratings = collect(PlanProgress::RATING_BANDS)->mapWithKeys(fn ($band) => [
            $band['label'] => $approved->where('evaluation.rating', $band['label'])->count(),
        ]);

        return [
            'total' => $employees->count(),
            'stages' => $stages->all(),
            'completion' => $employees->count() ? round($approved->count() / $employees->count() * 100, 1) : null,
            'average_score' => $this->averageFinalScore($employees),
            'ratings' => $ratings->all(),
        ];
    }

    private function averageFinalScore(Collection $employees): ?float
    {
        $scores = $employees->where('evaluation.stage', 'approved')->pluck('evaluation.final')->filter(fn ($s) => $s !== null);

        return $scores->isEmpty() ? null : round($scores->avg(), 1);
    }

    private function activityTotals(Collection $employees): array
    {
        return [
            'verified' => $employees->sum('verified'),
            'pending' => $employees->sum('pending'),
            'rejected' => $employees->sum('rejected'),
            'inactive' => $employees->where('inactive', true)->count(),
        ];
    }

    private function smartSummary(Collection $goals): array
    {
        $total = $goals->count();

        return [
            'total' => $total,
            'compliant' => $goals->where('smart_ok', true)->count(),
            'criteria' => collect(['S' => 'Specific', 'M' => 'Measurable', 'A' => 'Achievable', 'R' => 'Relevant', 'T' => 'Time-bound'])
                ->map(fn ($label, $key) => [
                    'label' => $label,
                    'met' => $goals->filter(fn ($g) => $g['smart'][$key])->count(),
                ])->all(),
        ];
    }

    /** Planning gaps an HR officer should chase, most structural first. */
    private function issues(Collection $orgGoals, Collection $departments, Collection $employees): array
    {
        $issues = [];

        foreach ($orgGoals->where('departments', 0) as $goal) {
            $issues[] = ['level' => 'danger', 'text' => "Organisation goal \"{$goal['name']}\" is not assigned to any department."];
        }

        if ($this->unplannedDepartments && $orgGoals->isNotEmpty()) {
            $issues[] = ['level' => 'warning', 'text' => count($this->unplannedDepartments).' department(s) have no plan under this organisational plan.'];
        }

        foreach ($departments as $department) {
            foreach ($department['goals'] as $goal) {
                if ($goal['contributors'] === 0) {
                    $issues[] = ['level' => 'danger', 'text' => "{$department['department']}: \"{$goal['name']}\" has no employee goals delivering it."];
                } elseif ($goal['coverage'] !== null && $goal['coverage'] < 100) {
                    $issues[] = ['level' => 'warning', 'text' => "{$department['department']}: only {$goal['coverage']}% of the \"{$goal['name']}\" target is assigned to staff."];
                }
            }

            if ($department['goals'] && abs($department['weight_total'] - 100) > 0.01) {
                $issues[] = ['level' => 'warning', 'text' => "{$department['department']}: goal weights total {$department['weight_total']}, not 100."];
            }

            $unplanned = $department['headcount'] - $department['staff_planned'];
            if ($unplanned > 0) {
                $issues[] = ['level' => 'info', 'text' => "{$department['department']}: {$unplanned} active staff have no plan yet."];
            }
        }

        $badWeights = $employees->filter(fn ($e) => $e['goals'] && abs($e['weight_total'] - 100) > 0.01)->count();
        if ($badWeights) {
            $issues[] = ['level' => 'warning', 'text' => "{$badWeights} employee plan(s) have goal weights that do not total 100."];
        }

        if ($pending = $employees->sum('pending')) {
            $issues[] = ['level' => 'info', 'text' => "{$pending} implementation entr".($pending === 1 ? 'y is' : 'ies are').' waiting for supervisor verification.'];
        }

        return $issues;
    }

    private function recentActivity(string $employeePlanId, int $limit = 8): Collection
    {
        return EmployeePlanImplementation::whereHas('employeePlanItem', fn ($q) => $q->where('employee_plan_id', $employeePlanId))
            ->with('employeePlanItem:id,item_name')
            ->latest('implementation_date')
            ->latest()
            ->limit($limit)
            ->get(['id', 'employee_plan_item_id', 'implementation_date', 'activity_title', 'quantity_achieved', 'unit', 'status', 'verification_score', 'evidence_path']);
    }
}

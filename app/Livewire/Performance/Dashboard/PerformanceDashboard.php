<?php

namespace App\Livewire\Performance\Dashboard;

use App\Models\OrganizationalPlan;
use App\Services\Performance\PerformanceDashboard as Dashboard;
use App\Services\Performance\PlanProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Top-down view of one organisational plan for HR officers and management:
 * organisation -> department -> employee, each level built from verified work below it.
 *
 * Users who can see every department start at the organisation level; others are taken
 * straight to their own department and cannot step above it.
 */
class PerformanceDashboard extends Component
{
    #[Url(as: 'plan')]
    public ?string $planId = null;

    #[Url(as: 'department')]
    public ?string $departmentPlanId = null;

    #[Url(as: 'employee')]
    public ?string $employeePlanId = null;

    /** Employee table filters on the department level */
    public string $search = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        abort_unless(Dashboard::canView(Auth::user()), 403);

        if (! $this->planId || ! OrganizationalPlan::whereKey($this->planId)->exists()) {
            $this->planId = Dashboard::defaultPlan(Dashboard::selectablePlans())?->id;
        }
    }

    public function updatedPlanId(): void
    {
        $this->departmentPlanId = null;
        $this->employeePlanId = null;
        $this->resetFilters();
    }

    public function openDepartment(string $departmentPlanId): void
    {
        $this->departmentPlanId = $departmentPlanId;
        $this->employeePlanId = null;
        $this->resetFilters();
    }

    public function openEmployee(string $employeePlanId): void
    {
        $this->employeePlanId = $employeePlanId;
    }

    public function showOrganisation(): void
    {
        $this->departmentPlanId = null;
        $this->employeePlanId = null;
    }

    public function showDepartment(): void
    {
        $this->employeePlanId = null;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
    }

    public function render()
    {
        $user = Auth::user();
        $plans = Dashboard::selectablePlans();
        $plan = $plans->firstWhere('id', $this->planId);
        $canSeeOrganisation = Dashboard::canViewOrganisation($user);

        $view = ['plans' => $plans, 'plan' => $plan, 'canSeeOrganisation' => $canSeeOrganisation];

        if (! $plan) {
            return view('livewire.performance.dashboard.performance-dashboard', $view + ['level' => 'empty']);
        }

        $dashboard = new Dashboard(OrganizationalPlan::findOrFail($plan->id), $user);
        $view += ['dashboard' => $dashboard, 'statuses' => PlanProgress::STATUSES];

        // Users limited to their own department never see the organisation level
        if (! $canSeeOrganisation && ! in_array($this->departmentPlanId, $dashboard->departmentPlanIds(), true)) {
            $this->departmentPlanId = $dashboard->ownDepartmentPlanId();
        }

        $employee = $this->employeePlanId ? $dashboard->employee($this->employeePlanId) : null;
        if ($employee && $employee['department_plan_id'] !== $this->departmentPlanId) {
            $this->departmentPlanId = $employee['department_plan_id'];
        }

        $department = $this->departmentPlanId ? $dashboard->department($this->departmentPlanId) : null;

        if ($employee) {
            return view('livewire.performance.dashboard.performance-dashboard', $view + [
                'level' => 'employee', 'employee' => $employee, 'department' => $department,
            ]);
        }

        if ($department) {
            return view('livewire.performance.dashboard.performance-dashboard', $view + [
                'level' => 'department', 'department' => $department, 'employees' => $this->filterEmployees($department['employees']),
            ]);
        }

        if ($canSeeOrganisation) {
            return view('livewire.performance.dashboard.performance-dashboard', $view + [
                'level' => 'organisation', 'organisation' => $dashboard->organisation(),
            ]);
        }

        return view('livewire.performance.dashboard.performance-dashboard', $view + ['level' => 'no-department']);
    }

    private function filterEmployees(array $employees): array
    {
        $search = mb_strtolower(trim($this->search));

        return array_values(array_filter($employees, fn ($e) => ($search === ''
                || str_contains(mb_strtolower($e['employee_name']), $search)
                || str_contains(mb_strtolower((string) $e['employee_no']), $search))
            && ($this->statusFilter === '' || $e['status'] === $this->statusFilter)));
    }
}

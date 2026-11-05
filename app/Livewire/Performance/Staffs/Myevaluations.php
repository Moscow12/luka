<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\PerformanceEvaluation;
use App\Services\Performance\EvaluationWorkflow;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Myevaluations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $employee;

    public $hasEmployeeRecord = false;

    public $plansReadyForEvaluation = [];

    // Modal states
    public $showSubmitModal = false;

    public $showDetailModal = false;

    // Form fields
    public $selectedPlanId;

    public $employee_comments;

    public $selectedEvaluation = null;

    // Filter
    public $filterStatus = '';

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->loadPlansReadyForEvaluation();
        }
    }

    public function loadPlansReadyForEvaluation()
    {
        if (! $this->employee) {
            return;
        }

        // Get active/completed plans that don't have an evaluation yet or have draft/rejected evaluations
        $this->plansReadyForEvaluation = EmployeePlan::where('employee_id', $this->employee->id)
            ->whereIn('status', ['active', 'completed'])
            ->whereDoesntHave('performanceEvaluation', function ($query) {
                $query->whereIn('status', ['submitted', 'under_review', 'approved']);
            })
            ->with('employeePlanItems')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openSubmitModal($planId)
    {
        $this->resetErrorBag();
        $this->selectedPlanId = $planId;
        $this->employee_comments = '';
        $this->showSubmitModal = true;
    }

    public function submitForEvaluation()
    {
        $this->validate([
            'selectedPlanId' => ['required', 'exists:employee_plans,id'],
            'employee_comments' => ['nullable', 'string'],
        ]);

        $plan = EmployeePlan::where('id', $this->selectedPlanId)
            ->where('employee_id', $this->employee->id)
            ->with('employeePlanItems')
            ->firstOrFail();

        // Check if plan has items
        if ($plan->employeePlanItems->isEmpty()) {
            session()->flash('error', 'Cannot submit a plan with no goals for evaluation.');

            return;
        }

        // Never submit into a level nobody can act on
        $firstLevel = EvaluationWorkflow::firstLevelOrder($this->employee);

        if ($firstLevel === null) {
            session()->flash('error', 'No approver is set up for your department yet, so this evaluation cannot be submitted. Please contact your administrator.');
            $this->showSubmitModal = false;

            return;
        }

        if (PerformanceEvaluation::where('employee_plan_id', $plan->id)->whereIn('status', ['submitted', 'under_review', 'approved'])->exists()) {
            session()->flash('error', 'This plan has already been submitted for evaluation.');
            $this->showSubmitModal = false;

            return;
        }

        // A rejected or draft evaluation is reused, so its approval history stays together
        $evaluation = PerformanceEvaluation::where('employee_plan_id', $plan->id)
            ->where('employee_id', $this->employee->id)
            ->whereIn('status', ['draft', 'rejected'])
            ->first() ?? new PerformanceEvaluation([
                'employee_plan_id' => $plan->id,
                'employee_id' => $this->employee->id,
            ]);

        $evaluation->fill([
            'self_score' => $evaluation->calculateSelfScore(),
            'task_score' => $evaluation->calculateTaskScore(),
            'employee_comments' => $this->employee_comments,
            'status' => 'submitted',
            'current_approval_level' => $firstLevel,
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
            'rejection_reason' => null,
        ])->save();

        session()->flash('success', 'Performance evaluation submitted successfully!');
        $this->showSubmitModal = false;
        $this->reset(['selectedPlanId', 'employee_comments']);
        $this->loadPlansReadyForEvaluation();
    }

    public function viewEvaluation($evaluationId)
    {
        $this->selectedEvaluation = PerformanceEvaluation::where('id', $evaluationId)
            ->where('employee_id', $this->employee->id)
            ->with(['employeePlan.employeePlanItems.implementations', 'approvals.approvalLevel', 'approvals.approver'])
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedEvaluation = null;
    }

    public function render()
    {
        $evaluations = PerformanceEvaluation::where('employee_id', $this->employee?->id)
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->with(['employeePlan', 'approvals'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.performance.staffs.myevaluations', [
            'evaluations' => $evaluations,
            'pendingEntriesForSelectedPlan' => $this->showSubmitModal && $this->selectedPlanId
                ? (new PerformanceEvaluation(['employee_plan_id' => $this->selectedPlanId]))->pendingEntriesCount()
                : 0,
        ]);
    }
}

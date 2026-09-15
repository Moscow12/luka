<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\approvalleveltodocument;
use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\PerformanceEvaluation;
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
        if (!$this->employee) return;

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

        // Calculate self score based on implementations
        $selfScore = $this->calculatePlanScore($plan);

        // Get the first approval level for Performance documents
        $firstApprovalLevel = approvalleveltodocument::where('document_type', 'Performance')
            ->where('is_active', true)
            ->with('approval_level')
            ->whereHas('approval_level', function ($q) {
                $q->where('is_active', true)->orderBy('level_order');
            })
            ->first();

        $currentLevel = $firstApprovalLevel ? 1 : 0;

        // Check if there's an existing draft/rejected evaluation
        $evaluation = PerformanceEvaluation::where('employee_plan_id', $plan->id)
            ->where('employee_id', $this->employee->id)
            ->whereIn('status', ['draft', 'rejected'])
            ->first();

        if ($evaluation) {
            // Update existing evaluation
            $evaluation->update([
                'self_score' => $selfScore,
                'employee_comments' => $this->employee_comments,
                'status' => 'submitted',
                'current_approval_level' => $currentLevel,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'rejection_reason' => null,
            ]);
        } else {
            // Create new evaluation
            PerformanceEvaluation::create([
                'employee_plan_id' => $plan->id,
                'employee_id' => $this->employee->id,
                'self_score' => $selfScore,
                'employee_comments' => $this->employee_comments,
                'status' => 'submitted',
                'current_approval_level' => $currentLevel,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
            ]);
        }

        session()->flash('success', 'Performance evaluation submitted successfully!');
        $this->showSubmitModal = false;
        $this->reset(['selectedPlanId', 'employee_comments']);
        $this->loadPlansReadyForEvaluation();
    }

    protected function calculatePlanScore(EmployeePlan $plan): float
    {
        $items = $plan->employeePlanItems;
        if ($items->isEmpty()) return 0;

        $totalWeightedScore = 0;
        $totalWeight = $items->sum('weight');

        foreach ($items as $item) {
            if ($item->target_value && $item->target_value > 0) {
                $achieved = $item->total_achieved ?? 0;
                $percentage = min(($achieved / $item->target_value) * 100, 100);
                $weightedScore = ($percentage * $item->weight) / 100;
                $totalWeightedScore += $weightedScore;
            }
        }

        return $totalWeight > 0 ? round(($totalWeightedScore / $totalWeight) * 100, 2) : 0;
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
        ]);
    }
}

<?php

namespace App\Livewire\Performance\Supervisor;

use App\Models\approvallevel;
use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\departments;
use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceEvaluationApproval;
use App\Services\Performance\EvaluationWorkflow;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ApproveEvaluations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $employee;

    public $hasEmployeeRecord = false;

    public $canApprove = false;

    public $myApprovalLevels = [];

    // Modal states
    public $showApprovalModal = false;

    public $showDetailModal = false;

    // Approval form fields
    #[Locked]
    public $selectedEvaluationId;

    public $supervisor_score;

    public $supervisor_comments;

    public $strengths;

    public $areas_for_improvement;

    public $recommendations;

    public $approval_status = 'approved';

    public $rejection_reason;

    // Detail view
    public $selectedEvaluation = null;

    // Filters
    public $filterStatus = 'submitted';

    public $filterDepartment = '';

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->loadApprovalLevels();

            if ($this->canApprove) {
                EvaluationWorkflow::releaseStranded();
            }
        }
    }

    public function loadApprovalLevels()
    {
        if (! $this->employee) {
            return;
        }

        // Get approval levels assigned to this employee for Performance documents
        $assignedLevels = approvalleveltoemployee::where('employee_id', $this->employee->id)
            ->where('is_active', true)
            ->pluck('approval_level_id');

        // Check if any of these levels are mapped to Performance documents
        $performanceLevels = approvalleveltodocument::where('document_type', 'Performance')
            ->where('is_active', true)
            ->whereIn('approval_level_id', $assignedLevels)
            ->pluck('approval_level_id');

        $this->myApprovalLevels = approvallevel::whereIn('id', $performanceLevels)
            ->where('is_active', true)
            ->orderBy('level_order')
            ->get();

        $this->canApprove = $this->myApprovalLevels->isNotEmpty();
    }

    public function getPendingEvaluations()
    {
        if (! $this->canApprove || ! $this->employee) {
            return collect();
        }

        return EvaluationWorkflow::pendingFor(Auth::user())
            ->when($this->filterDepartment, fn ($evaluations) => $evaluations->where('employee.department_id', $this->filterDepartment)->values());
    }

    /** Ids come from the client, so only an evaluation this user may act on now is ever loaded. */
    private function findActionableEvaluation($evaluationId): PerformanceEvaluation
    {
        $evaluation = PerformanceEvaluation::findOrFail($evaluationId);

        abort_unless(EvaluationWorkflow::canApprove(Auth::user(), $evaluation), 403);

        return $evaluation;
    }

    public function openApprovalModal($evaluationId)
    {
        $evaluation = $this->findActionableEvaluation($evaluationId);

        $this->resetErrorBag();
        $this->selectedEvaluationId = $evaluation->id;
        $this->supervisor_score = $evaluation->supervisor_score ?? $evaluation->calculateSelfScore();
        $this->supervisor_comments = $evaluation->supervisor_comments;
        $this->strengths = $evaluation->strengths;
        $this->areas_for_improvement = $evaluation->areas_for_improvement;
        $this->recommendations = $evaluation->recommendations;
        $this->approval_status = 'approved';
        $this->rejection_reason = '';
        $this->showApprovalModal = true;
    }

    public function viewEvaluation($evaluationId)
    {
        $this->selectedEvaluation = $this->findActionableEvaluation($evaluationId)->load([
            'employee.department',
            'employeePlan.employeePlanItems.implementations',
            'approvals.approvalLevel',
            'approvals.approver',
        ]);

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedEvaluation = null;
    }

    public function processApproval()
    {
        $rules = [
            'selectedEvaluationId' => ['required', 'exists:performance_evaluations,id'],
            'supervisor_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'supervisor_comments' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'approval_status' => ['required', 'in:approved,rejected'],
        ];

        if ($this->approval_status === 'rejected') {
            $rules['rejection_reason'] = ['required', 'string', 'min:10'];
        }

        $this->validate($rules);

        $evaluation = PerformanceEvaluation::findOrFail($this->selectedEvaluationId);

        // Verify this user can act on this evaluation at its current level
        $currentLevelOrder = $evaluation->current_approval_level;
        $canApproveAtLevel = EvaluationWorkflow::approvalLevelFor(Auth::user(), $evaluation);

        if (! $canApproveAtLevel) {
            session()->flash('error', 'You are not authorized to approve this evaluation.');

            return;
        }

        if ($this->approval_status === 'approved' && $evaluation->pendingEntriesCount() > 0) {
            $this->addError('approval_status', 'Verify or reject all of this employee\'s pending entries before approving.');

            return;
        }

        // Create approval record
        PerformanceEvaluationApproval::create([
            'performance_evaluation_id' => $evaluation->id,
            'approval_level_id' => $canApproveAtLevel->id,
            'approved_by' => $this->employee->id,
            'status' => $this->approval_status,
            'remarks' => $this->approval_status === 'rejected' ? $this->rejection_reason : $this->supervisor_comments,
            'approved_at' => now(),
        ]);

        // Update evaluation
        $updateData = [
            'supervisor_score' => $this->supervisor_score,
            'supervisor_comments' => $this->supervisor_comments,
            'strengths' => $this->strengths,
            'areas_for_improvement' => $this->areas_for_improvement,
            'recommendations' => $this->recommendations,
        ];

        if ($this->approval_status === 'rejected') {
            $updateData['status'] = 'rejected';
            $updateData['rejection_reason'] = $this->rejection_reason;
        } else {
            // The self score follows the work verified so far
            $selfScore = $evaluation->calculateSelfScore();
            $taskScore = $evaluation->calculateTaskScore();
            $updateData['self_score'] = $selfScore;
            $updateData['task_score'] = $taskScore;

            $nextLevelOrder = EvaluationWorkflow::nextLevelOrder($evaluation->employee, $currentLevelOrder);

            if ($nextLevelOrder !== null) {
                // Move to next approval level
                $updateData['status'] = 'under_review';
                $updateData['current_approval_level'] = $nextLevelOrder;
            } else {
                // Final approval
                $updateData['status'] = 'approved';
                $updateData['final_score'] = PerformanceEvaluation::combineScores($selfScore, (float) $this->supervisor_score, $taskScore);
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
            }
        }

        $evaluation->update($updateData);

        $message = $this->approval_status === 'approved'
            ? 'Evaluation approved successfully!'
            : 'Evaluation rejected.';

        session()->flash('success', $message);
        $this->showApprovalModal = false;
        $this->resetApprovalForm();
    }

    public function resetApprovalForm()
    {
        $this->reset([
            'selectedEvaluationId', 'supervisor_score', 'supervisor_comments',
            'strengths', 'areas_for_improvement', 'recommendations',
            'rejection_reason',
        ]);
        $this->approval_status = 'approved';
    }

    public function getDepartments()
    {
        $coveredIds = EvaluationWorkflow::coveredDepartmentIds(Auth::user());

        return departments::when($coveredIds !== null, fn ($q) => $q->whereIn('id', $coveredIds))->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.performance.supervisor.approve-evaluations', [
            'pendingEvaluations' => $this->getPendingEvaluations(),
            'departments' => $this->canApprove ? $this->getDepartments() : collect(),
            'selectedEvaluationForReview' => $this->showApprovalModal && $this->selectedEvaluationId
                ? PerformanceEvaluation::find($this->selectedEvaluationId)
                : null,
        ]);
    }
}

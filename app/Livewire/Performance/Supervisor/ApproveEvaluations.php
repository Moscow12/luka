<?php

namespace App\Livewire\Performance\Supervisor;

use App\Models\approvallevel;
use App\Models\approvalleveltoemployee;
use App\Models\approvalleveltodocument;
use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceEvaluationApproval;
use Illuminate\Support\Facades\Auth;
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
        }
    }

    public function loadApprovalLevels()
    {
        if (!$this->employee) return;

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
        if (!$this->canApprove || !$this->employee) {
            return collect();
        }

        $levelOrders = $this->myApprovalLevels->pluck('level_order')->toArray();

        return PerformanceEvaluation::whereIn('status', ['submitted', 'under_review'])
            ->whereIn('current_approval_level', $levelOrders)
            ->when($this->filterDepartment, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('department_id', $this->filterDepartment);
                });
            })
            ->with(['employee.department', 'employeePlan.employeePlanItems'])
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    public function openApprovalModal($evaluationId)
    {
        $this->resetErrorBag();
        $this->selectedEvaluationId = $evaluationId;

        $evaluation = PerformanceEvaluation::with(['employeePlan.employeePlanItems'])->find($evaluationId);
        if ($evaluation) {
            $this->supervisor_score = $evaluation->supervisor_score ?? $evaluation->self_score;
            $this->supervisor_comments = $evaluation->supervisor_comments;
            $this->strengths = $evaluation->strengths;
            $this->areas_for_improvement = $evaluation->areas_for_improvement;
            $this->recommendations = $evaluation->recommendations;
        }

        $this->approval_status = 'approved';
        $this->rejection_reason = '';
        $this->showApprovalModal = true;
    }

    public function viewEvaluation($evaluationId)
    {
        $this->selectedEvaluation = PerformanceEvaluation::with([
            'employee.department',
            'employeePlan.employeePlanItems.implementations',
            'approvals.approvalLevel',
            'approvals.approver'
        ])->find($evaluationId);

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

        // Verify this user can approve at this level
        $currentLevelOrder = $evaluation->current_approval_level;
        $canApproveAtLevel = $this->myApprovalLevels->where('level_order', $currentLevelOrder)->first();

        if (!$canApproveAtLevel) {
            session()->flash('error', 'You are not authorized to approve at this level.');
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
            // Check if there are more approval levels
            $nextLevel = approvallevel::where('is_active', true)
                ->where('level_order', '>', $currentLevelOrder)
                ->whereHas('approvalleveltodocuments', function ($q) {
                    $q->where('document_type', 'Performance')->where('is_active', true);
                })
                ->orderBy('level_order')
                ->first();

            if ($nextLevel) {
                // Move to next approval level
                $updateData['status'] = 'under_review';
                $updateData['current_approval_level'] = $nextLevel->level_order;
            } else {
                // Final approval
                $updateData['status'] = 'approved';
                $updateData['final_score'] = ($evaluation->self_score + $this->supervisor_score) / 2;
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
            'rejection_reason'
        ]);
        $this->approval_status = 'approved';
    }

    public function getDepartments()
    {
        return \App\Models\departments::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.performance.supervisor.approve-evaluations', [
            'pendingEvaluations' => $this->getPendingEvaluations(),
            'departments' => $this->getDepartments(),
        ]);
    }
}

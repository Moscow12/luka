<?php

namespace App\Livewire\Hr\Leave;

use App\Models\approvallevel;
use App\Models\approvalleveltoemployee;
use App\Models\approvalleveltodocument;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Leaveapproval extends Component
{
    use WithPagination;

    public $statusFilter = 'Awaiting';
    public $selectedLeave = null;
    public $showModal = false;
    public $comments = '';
    public $actionType = '';
    public $currentApprovalLevel = null;
    public $userApprovalLevels = [];

    public function mount()
    {
        $this->loadUserApprovalLevels();
    }

    public function loadUserApprovalLevels()
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if ($employee) {
            $this->userApprovalLevels = approvalleveltoemployee::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->with('approval_level')
                ->get()
                ->pluck('approval_level.id')
                ->toArray();
        }
    }

    public function openApprovalModal($leaveId, $action)
    {
        $this->selectedLeave = Employeeleaves::with(['employee', 'leave', 'approvalnote.approval_level'])->findOrFail($leaveId);
        $this->actionType = $action;
        $this->comments = '';

        // Get current approval level needed for this leave
        $this->currentApprovalLevel = $this->getCurrentApprovalLevel($this->selectedLeave);

        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
        $this->comments = '';
        $this->actionType = '';
        $this->currentApprovalLevel = null;
    }

    public function getCurrentApprovalLevel($leave)
    {
        // Get all approval levels for Leave document type, ordered by level_order
        $approvalLevels = approvalleveltodocument::where('document_type', 'Leave')
            ->where('is_active', true)
            ->with('approval_level')
            ->get()
            ->pluck('approval_level')
            ->filter()
            ->sortBy('level_order')
            ->values();

        if ($approvalLevels->isEmpty()) {
            return null;
        }

        // Get existing approvals for this leave
        $existingApprovals = $leave->approvalnote()->with('approval_level')->get();

        // If any approval was rejected, return null (already rejected)
        if ($existingApprovals->where('status', 'rejected')->isNotEmpty()) {
            return null;
        }

        // Find the next level that needs approval
        foreach ($approvalLevels as $level) {
            $alreadyApproved = $existingApprovals->where('approval_level_id', $level->id)
                ->where('status', 'approved')
                ->isNotEmpty();

            if (!$alreadyApproved) {
                return $level;
            }
        }

        return null; // All levels approved
    }

    public function processApproval()
    {
        if (!$this->selectedLeave || !$this->currentApprovalLevel) {
            return;
        }

        // Check if user has permission for this approval level
        if (!in_array($this->currentApprovalLevel->id, $this->userApprovalLevels)) {
            session()->flash('error', 'You do not have permission to approve at this level!');
            return;
        }

        $approvalStatus = $this->actionType === 'approve' ? 'approved' : 'rejected';

        // Create approval record
        leaverequestapproval::create([
            'leave_request_id' => $this->selectedLeave->id,
            'approval_level_id' => $this->currentApprovalLevel->id,
            'approver_id' => Auth::id(),
            'status' => $approvalStatus,
            'comments' => $this->comments,
            'approved_at' => now(),
        ]);

        // Update leave status based on approval flow
        $newStatus = $this->determineLeaveStatus($this->selectedLeave, $approvalStatus);

        $this->selectedLeave->update([
            'status' => $newStatus,
        ]);

        $statusMessage = $approvalStatus === 'approved' ? 'approved' : 'rejected';
        session()->flash('success', "Leave request has been {$statusMessage}!");
        $this->closeModal();
        $this->dispatch('close-modal');
        $this->dispatch('refreshNotifications');
    }

    public function determineLeaveStatus($leave, $approvalStatus)
    {
        if ($approvalStatus === 'rejected') {
            return 'Rejected';
        }

        // Get all approval levels for Leave document type
        $approvalLevels = approvalleveltodocument::where('document_type', 'Leave')
            ->where('is_active', true)
            ->with('approval_level')
            ->get()
            ->pluck('approval_level')
            ->filter()
            ->sortBy('level_order')
            ->values();

        if ($approvalLevels->isEmpty()) {
            return 'Approved';
        }

        // Get current approvals count (including the one just created)
        $approvedCount = leaverequestapproval::where('leave_request_id', $leave->id)
            ->where('status', 'approved')
            ->count();

        $totalLevels = $approvalLevels->count();

        // Determine status based on level
        if ($approvedCount >= $totalLevels) {
            return 'Approved'; // All levels approved
        } elseif ($approvedCount === 1) {
            return 'Active'; // First level approved
        } else {
            return 'Awaiting'; // Still waiting for more approvals
        }
    }

    public function render()
    {
        // Get logged-in user's employee record
        $employee = Employee::where('user_id', Auth::id())->first();

        $canApprove = !empty($this->userApprovalLevels);
        $pendingLeaves = collect();

        if ($canApprove) {
            // Get leave requests with approval notes
            $leavesQuery = Employeeleaves::with(['employee', 'leave', 'approvalnote.approval_level', 'approvalnote.approver'])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($this->statusFilter === 'Awaiting' || $this->statusFilter === 'Active') {
                // Show leaves that are awaiting or active
                $leavesQuery->whereIn('status', ['Awaiting', 'Active']);
            } else {
                $leavesQuery->where('status', $this->statusFilter);
            }

            $pendingLeaves = $leavesQuery->paginate(10);

            // For each leave, determine if current user can approve at current level
            $pendingLeaves->getCollection()->transform(function ($leave) {
                $leave->canUserApprove = false;
                $leave->nextApprovalLevel = $this->getCurrentApprovalLevel($leave);

                if ($leave->nextApprovalLevel) {
                    $leave->canUserApprove = in_array($leave->nextApprovalLevel->id, $this->userApprovalLevels);
                }

                return $leave;
            });
        }

        return view('livewire.hr.leave.leaveapproval', [
            'pendingLeaves' => $pendingLeaves,
            'canApprove' => $canApprove,
        ]);
    }
}

<?php

namespace App\Livewire\Hr\Leave;

use App\Models\approvalleveltoemployee;
use App\Models\approvalleveltodocument;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Leaveapproval extends Component
{
    use WithPagination;

    public $statusFilter = 'Awaiting';
    public $selectedLeave = null;
    public $showModal = false;
    public $comments = '';
    public $userApprovalLevels = [];

    protected $paginationTheme = 'bootstrap';

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
                ->filter()
                ->toArray();
        }
    }

    public function approveLeave($leaveId)
    {
        $leave = Employeeleaves::with(['employee', 'leave'])->findOrFail($leaveId);
        $currentLevel = $this->getCurrentApprovalLevel($leave);

        if (!$currentLevel) {
            session()->flash('error', 'No approval level found or leave already processed.');
            return;
        }

        if (!in_array($currentLevel->id, $this->userApprovalLevels)) {
            session()->flash('error', 'You do not have permission to approve at this level.');
            return;
        }

        // Create approval record
        leaverequestapproval::create([
            'leave_request_id' => $leave->id,
            'approval_level_id' => $currentLevel->id,
            'approver_id' => Auth::id(),
            'status' => 'approved',
            'comments' => 'Approved',
            'approved_at' => now(),
        ]);

        // Update leave status
        $newStatus = $this->determineLeaveStatus($leave, 'approved');
        $leave->update(['status' => $newStatus]);

        session()->flash('success', 'Leave request has been approved successfully!');
        $this->dispatch('refreshNotifications');
    }

    public function openRejectModal($leaveId)
    {
        $this->selectedLeave = Employeeleaves::with(['employee', 'leave'])->findOrFail($leaveId);
        $this->comments = '';
        $this->showModal = true;
    }

    public function rejectLeave()
    {
        $this->validate([
            'comments' => 'required|string|min:5|max:500',
        ], [
            'comments.required' => 'Please provide a reason for rejection.',
            'comments.min' => 'Rejection reason must be at least 5 characters.',
        ]);

        if (!$this->selectedLeave) {
            session()->flash('error', 'No leave request selected.');
            return;
        }

        $currentLevel = $this->getCurrentApprovalLevel($this->selectedLeave);

        if (!$currentLevel) {
            session()->flash('error', 'No approval level found or leave already processed.');
            $this->closeModal();
            return;
        }

        if (!in_array($currentLevel->id, $this->userApprovalLevels)) {
            session()->flash('error', 'You do not have permission to reject at this level.');
            $this->closeModal();
            return;
        }

        // Create rejection record
        leaverequestapproval::create([
            'leave_request_id' => $this->selectedLeave->id,
            'approval_level_id' => $currentLevel->id,
            'approver_id' => Auth::id(),
            'status' => 'rejected',
            'comments' => $this->comments,
            'approved_at' => now(),
        ]);

        // Update leave status to rejected
        $this->selectedLeave->update(['status' => 'Rejected']);

        session()->flash('success', 'Leave request has been rejected.');
        $this->closeModal();
        $this->dispatch('refreshNotifications');
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
        $this->comments = '';
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
        $existingApprovals = leaverequestapproval::where('leave_request_id', $leave->id)
            ->with('approval_level')
            ->get();

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
            return 'approved';
        }

        // Get current approvals count (including the one just created)
        $approvedCount = leaverequestapproval::where('leave_request_id', $leave->id)
            ->where('status', 'approved')
            ->count();

        $totalLevels = $approvalLevels->count();

        // Determine status based on level
        if ($approvedCount >= $totalLevels) {
            return 'approved';
        } elseif ($approvedCount === 1) {
            return 'Active';
        } else {
            return 'Awaiting';
        }
    }

    public function render()
    {
        $canApprove = !empty($this->userApprovalLevels);
        $pendingLeaves = collect();

        if ($canApprove) {
            $leavesQuery = Employeeleaves::with(['employee', 'leave', 'approvalnote.approval_level', 'approvalnote.approver'])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($this->statusFilter === 'Awaiting') {
                $leavesQuery->where(function ($q) {
                    $q->whereRaw('LOWER(status) IN (?, ?, ?)', ['awaiting', 'pending', 'active']);
                });
            } elseif ($this->statusFilter === 'Active') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['active']);
            } elseif ($this->statusFilter === 'Approved') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['approved']);
            } elseif ($this->statusFilter === 'Rejected') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['rejected']);
            }

            $pendingLeaves = $leavesQuery->paginate(10);

            // For each leave, determine if current user can approve
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

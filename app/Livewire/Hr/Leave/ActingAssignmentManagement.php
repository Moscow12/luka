<?php

namespace App\Livewire\Hr\Leave;

use App\Models\ActingAssignment;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ActingAssignmentManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedAssignment = null;
    public $showModal = false;
    public $rejectionReason = '';
    public $showRejectModal = false;
    public $assignmentToReject = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function viewAssignmentDetails($assignmentId)
    {
        $this->selectedAssignment = ActingAssignment::with([
            'leaveRequest.leave',
            'employeeOnLeave',
            'actingEmployee',
            'actingDesignation',
            'actingDepartment',
            'approvedBy',
            'addedBy'
        ])->findOrFail($assignmentId);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedAssignment = null;
    }

    public function approveAssignment($assignmentId)
    {
        $user = Auth::user();

        if (!$user->can('approve-leave') && !$user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to approve acting assignments.');
            return;
        }

        $assignment = ActingAssignment::findOrFail($assignmentId);
        $assignment->approve($user->id);

        session()->flash('success', 'Acting assignment approved successfully.');
        $this->closeModal();
    }

    public function openRejectModal($assignmentId)
    {
        $this->assignmentToReject = $assignmentId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->assignmentToReject = null;
        $this->rejectionReason = '';
    }

    public function rejectAssignment()
    {
        $user = Auth::user();

        if (!$user->can('approve-leave') && !$user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to reject acting assignments.');
            return;
        }

        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:500',
        ]);

        $assignment = ActingAssignment::findOrFail($this->assignmentToReject);
        $assignment->reject($user->id, $this->rejectionReason);

        session()->flash('success', 'Acting assignment rejected.');
        $this->closeRejectModal();
        $this->closeModal();
    }

    public function activateAssignment($assignmentId)
    {
        $assignment = ActingAssignment::findOrFail($assignmentId);
        $assignment->activate();

        session()->flash('success', 'Acting assignment activated.');
        $this->closeModal();
    }

    public function completeAssignment($assignmentId)
    {
        $assignment = ActingAssignment::findOrFail($assignmentId);
        $assignment->complete();

        session()->flash('success', 'Acting assignment marked as completed.');
        $this->closeModal();
    }

    public function cancelAssignment($assignmentId)
    {
        $assignment = ActingAssignment::findOrFail($assignmentId);
        $assignment->cancel();

        session()->flash('success', 'Acting assignment cancelled.');
        $this->closeModal();
    }

    public function render()
    {
        $assignmentsQuery = ActingAssignment::with([
            'leaveRequest.leave',
            'employeeOnLeave',
            'actingEmployee',
            'actingDesignation',
            'approvedBy'
        ])->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $assignmentsQuery->where(function ($query) {
                $query->whereHas('employeeOnLeave', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })->orWhereHas('actingEmployee', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $assignmentsQuery->where('status', $this->statusFilter);
        }

        $assignments = $assignmentsQuery->paginate(15);

        $user = Auth::user();
        $canApprove = $user && ($user->can('approve-leave') || $user->isSuperAdmin());

        return view('livewire.hr.leave.acting-assignment-management', [
            'assignments' => $assignments,
            'canApprove' => $canApprove,
        ]);
    }
}

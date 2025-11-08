<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\approvalleveltoemployee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Leaveapproval extends Component
{
    use WithPagination;

    public $statusFilter = 'Awaiting';
    public $selectedLeave = null;
    public $showModal = false;
    public $approvalNote = '';
    public $actionType = '';

    public function mount()
    {
        //
    }

    public function openApprovalModal($leaveId, $action)
    {
        $this->selectedLeave = Employeeleaves::with(['employee', 'leave'])->findOrFail($leaveId);
        $this->actionType = $action;
        $this->approvalNote = '';
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
        $this->approvalNote = '';
        $this->actionType = '';
    }

    public function processApproval()
    {
        if (!$this->selectedLeave) {
            return;
        }

        $status = $this->actionType === 'approve' ? 'Approved' : 'Rejected';

        $this->selectedLeave->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_note' => $this->approvalNote,
        ]);

        session()->flash('success', "Leave request has been {$status}!");
        $this->closeModal();
        $this->dispatch('close-modal');
        $this->dispatch('refreshNotifications');
    }

    public function render()
    {
        // Get logged-in user's employee record
        $employee = Employee::where('user_id', Auth::id())->first();

        $canApprove = false;
        $pendingLeaves = collect();

        if ($employee) {
            // Check if user has approval permissions for Leave documents
            $approvalLevels = approvalleveltoemployee::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->with('approval_level.approvalleveltodocuments')
                ->get();

            $documentTypes = [];
            foreach ($approvalLevels as $mapping) {
                if ($mapping->approval_level) {
                    foreach ($mapping->approval_level->approvalleveltodocuments as $doc) {
                        if ($doc->is_active) {
                            $documentTypes[] = $doc->document_type;
                        }
                    }
                }
            }

            $canApprove = in_array('Leave', array_unique($documentTypes));

            if ($canApprove) {
                // Get leave requests based on status filter
                $pendingLeaves = Employeeleaves::with(['employee', 'leave'])
                    ->where('status', $this->statusFilter)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            }
        }

        return view('livewire.hr.leave.leaveapproval', [
            'pendingLeaves' => $pendingLeaves,
            'canApprove' => $canApprove,
        ]);
    }
}

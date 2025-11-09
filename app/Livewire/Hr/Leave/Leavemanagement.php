<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Leavemanagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedLeave = null;
    public $showModal = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function viewLeaveDetails($leaveId)
    {
        $this->selectedLeave = Employeeleaves::with([
            'employee',
            'leave',
            'approvalnote.approval_level',
            'approvalnote.approver'
        ])->findOrFail($leaveId);

        $this->showModal = true;
        $this->dispatch('open-leave-modal');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
    }

    public function getLeaveBalance($employeeId, $leaveId)
    {
        $employee = Employee::find($employeeId);
        $leave = Leaves::find($leaveId);

        if (!$employee || !$leave) {
            return [
                'entitled' => 0,
                'used' => 0,
                'balance' => 0
            ];
        }

        // Calculate used days (only approved leaves)
        $usedDays = Employeeleaves::where('employee_id', $employeeId)
            ->where('leave_id', $leaveId)
            ->where('status', 'Approved')
            ->sum('number_of_days');

        $entitled = $leave->days ?? 0;
        $balance = $entitled - $usedDays;

        return [
            'entitled' => $entitled,
            'used' => $usedDays,
            'balance' => max(0, $balance)
        ];
    }

    public function render()
    {
        $leavesQuery = Employeeleaves::with([
            'employee',
            'leave',
            'approvalnote.approval_level'
        ])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $leavesQuery->whereHas('employee', function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('employee_number', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $leavesQuery->where('status', $this->statusFilter);
        }

        $leaves = $leavesQuery->paginate(15);

        // Calculate leave balance for each leave
        $leaves->getCollection()->transform(function ($leave) {
            $leave->leaveBalance = $this->getLeaveBalance($leave->employee_id, $leave->leave_id);
            return $leave;
        });

        return view('livewire.hr.leave.leavemanagement', [
            'leaves' => $leaves,
        ]);
    }
}

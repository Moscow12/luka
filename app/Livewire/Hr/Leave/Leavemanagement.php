<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Leavemanagement extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = 'all';

    public $leaveTypeFilter = 'all';

    public $dateFrom = '';

    public $dateTo = '';

    public $selectedLeave = null;

    public $selectedLeaveBalance = ['entitled' => 0, 'used' => 0, 'balance' => 0];

    public $showModal = false;

    public $rejectionReason = '';

    public $showRejectModal = false;

    public $leaveToReject = null;

    public function mount()
    {
        // Default to the current month's leave requests.
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingLeaveTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'leaveTypeFilter', 'dateFrom', 'dateTo']);
        $this->statusFilter = 'all';
        $this->leaveTypeFilter = 'all';
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function viewLeaveDetails($leaveId)
    {
        $this->selectedLeave = Employeeleaves::with([
            'employee',
            'leave',
            'approvalnote.approval_level',
            'approvalnote.approver',
        ])->findOrFail($leaveId);

        // Store balance in its own persisted property — a dynamic attribute on
        // the model would not survive Livewire's re-render (e.g. on search).
        $this->selectedLeaveBalance = $this->getLeaveBalance(
            $this->selectedLeave->employee_id,
            $this->selectedLeave->leave_id
        );

        $this->showModal = true;
        $this->dispatch('open-leave-modal');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
        $this->selectedLeaveBalance = ['entitled' => 0, 'used' => 0, 'balance' => 0];
    }

    public function approveLeave($leaveId)
    {
        $user = Auth::user();

        if (! $user->can('approve-leave') && ! $user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to approve leave requests.');

            return;
        }

        $leave = Employeeleaves::findOrFail($leaveId);
        $leave->update([
            'status' => 'Approved',
        ]);

        session()->flash('success', 'Leave request approved successfully.');
        $this->closeModal();
    }

    public function openRejectModal($leaveId)
    {
        $this->leaveToReject = $leaveId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->leaveToReject = null;
        $this->rejectionReason = '';
    }

    public function rejectLeave()
    {
        $user = Auth::user();

        if (! $user->can('approve-leave') && ! $user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to reject leave requests.');

            return;
        }

        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:500',
        ]);

        $leave = Employeeleaves::findOrFail($this->leaveToReject);
        $leave->update([
            'status' => 'Rejected',
            'comments' => $this->rejectionReason,
        ]);

        session()->flash('success', 'Leave request rejected.');
        $this->closeRejectModal();
        $this->closeModal();
    }

    public function getLeaveBalance($employeeId, $leaveId)
    {
        $employee = Employee::find($employeeId);
        $leave = Leaves::find($leaveId);

        if (! $employee || ! $leave) {
            return [
                'entitled' => 0,
                'used' => 0,
                'balance' => 0,
            ];
        }

        // Calculate used days (only approved leaves)
        $usedDays = Employeeleaves::where('employee_id', $employeeId)
            ->where('leave_id', $leaveId)
            ->where('status', 'Approved')
            ->sum('days');

        $entitled = $leave->days ?? 0;
        $balance = $entitled - $usedDays;

        return [
            'entitled' => $entitled,
            'used' => $usedDays,
            'balance' => max(0, $balance),
        ];
    }

    public function render()
    {
        $leavesQuery = Employeeleaves::with([
            'employee',
            'leave',
            'approvalnote.approval_level',
        ])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $term = '%'.$this->search.'%';
            $leavesQuery->whereHas('employee', function ($query) use ($term) {
                $query->where('first_name', 'like', $term)
                    ->orWhere('middle_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('employee_no', 'like', $term)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]);
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $leavesQuery->where('status', $this->statusFilter);
        }

        // Leave type filter
        if ($this->leaveTypeFilter !== 'all') {
            $leavesQuery->where('leave_id', $this->leaveTypeFilter);
        }

        // Date range filter — show leaves whose period overlaps [dateFrom, dateTo].
        if ($this->dateFrom) {
            $leavesQuery->whereDate('end_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $leavesQuery->whereDate('start_date', '<=', $this->dateTo);
        }

        $leaves = $leavesQuery->paginate(15);

        // Calculate leave balance for each leave
        $leaves->getCollection()->transform(function ($leave) {
            $leave->leaveBalance = $this->getLeaveBalance($leave->employee_id, $leave->leave_id);

            return $leave;
        });

        $user = Auth::user();
        $canApprove = $user && ($user->can('approve-leave') || $user->isSuperAdmin());

        return view('livewire.hr.leave.leavemanagement', [
            'leaves' => $leaves,
            'canApprove' => $canApprove,
            'leaveTypes' => Leaves::orderBy('name')->get(),
        ]);
    }
}

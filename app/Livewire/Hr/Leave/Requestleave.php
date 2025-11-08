<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Requestleave extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $leave_id, $start_date, $end_date, $days, $travel_to, $othercontact, $comments;
    public $employee, $employee_id, $leaveslist = [], $leaves = [], $errorMessage, $status = 'Awaiting', $available_days;
    public $hasEmployeeRecord = false;
    public $editingLeaveId = null;

    public function mount()
    {
        // Check if logged-in user is connected to an employee record
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->employee_id = $this->employee->id;
            $this->leaveslist = Leaves::all();
            $this->listdata();
        }
    }

    public function listdata()
    {
        if (!$this->hasEmployeeRecord) return;

        $this->leaves = Employeeleaves::where('employee_id', $this->employee_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        if (!$this->hasEmployeeRecord) return;

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $this->editingLeaveId = $id;
            $leave = Employeeleaves::findOrFail($id);
            $this->leave_id = $leave->leave_id;
            $this->start_date = $leave->start_date;
            $this->end_date = $leave->end_date;
            $this->days = $leave->days;
            $this->travel_to = $leave->travel_to;
            $this->othercontact = $leave->othercontact;
            $this->comments = $leave->comments;
        } else {
            $this->editingLeaveId = null;
            $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments']);
        }
    }

    public function save()
    {
        if (!$this->hasEmployeeRecord) return;

        $this->validate([
            'leave_id' => ['required'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'days' => ['required', 'integer', 'min:1'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ]);

        Employeeleaves::create([
            'leave_id' => $this->leave_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $this->days,
            'travel_to' => $this->travel_to,
            'othercontact' => $this->othercontact,
            'status' => $this->status,
            'comments' => $this->comments,
            'added_by' => Auth::id(),
            'employee_id' => $this->employee_id,
        ]);

        session()->flash('success', 'Leave request submitted successfully!');
        $this->showModal = false;
        $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments']);
        $this->listdata();
    }

    public function update()
    {
        if (!$this->hasEmployeeRecord || !$this->editingLeaveId) return;

        $this->validate([
            'leave_id' => ['required'],
            'start_date' => ['required', 'date'],
            'days' => ['required', 'integer', 'min:1'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ]);

        $leave = Employeeleaves::where('id', $this->editingLeaveId)
            ->where('employee_id', $this->employee_id)
            ->firstOrFail();

        $leave->update([
            'leave_id' => $this->leave_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $this->days,
            'travel_to' => $this->travel_to,
            'othercontact' => $this->othercontact,
            'comments' => $this->comments,
        ]);

        session()->flash('success', 'Leave request updated successfully!');
        $this->showModal = false;
        $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments', 'editingLeaveId']);
        $this->listdata();
    }

    public function delete($uuid)
    {
        if (!$this->hasEmployeeRecord) return;

        $leave = Employeeleaves::where('id', $uuid)
            ->where('employee_id', $this->employee_id)
            ->firstOrFail();

        $leave->delete();
        $this->listdata();
        session()->flash('success', 'Leave request deleted successfully!');
    }

    
    public function updatedDays()
    {
        $this->calculateEndDate();
    }

    public function updatedStartDate()
    {
        $this->calculateEndDate();
    }
    public function calculateLeaveBalance()
    {
        if (!$this->leave_id || !$this->hasEmployeeRecord) return;

        $leave = Leaves::find($this->leave_id);

        if (!$leave) {
            $this->available_days = null;
            return;
        }

        $used = Employeeleaves::where('employee_id', $this->employee_id)
            ->where('leave_id', $this->leave_id)
            ->where('status', '!=', 'Rejected')
            ->sum('days');

        $this->available_days = max($leave->days - $used, 0);
    }

    public function calculateEndDate()
    {
        $this->errorMessage = null;

        if ($this->start_date && $this->days && $this->leave_id) {
            $this->calculateLeaveBalance();

            if ($this->available_days <= 0) {
                $this->errorMessage = "You have no remaining days for this leave type.";
                $this->end_date = null;
                return;
            }

            if ($this->days > $this->available_days) {
                $this->errorMessage = "You only have {$this->available_days} leave days remaining.";
                $this->end_date = null;
                return;
            }

            $start = Carbon::parse($this->start_date);
            $this->end_date = $start->copy()->addDays($this->days + 1)->toDateString();
        } else {
            $this->end_date = null;
        }
    }

    public function render()
    {
        return view('livewire.hr.leave.requestleave');
    }
}

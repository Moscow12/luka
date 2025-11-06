<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Leave extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $leave_id, $start_date, $end_date, $days, $travel_to, $othercontact, $comments, $added_by;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;
    public $employee_id, $leaveslist=[], $leaves=[], $errorMessage,$status='Awaiting', $available_days;

    public function mount($id=null)
    {
        $staff = Employee::findOrFail($id);
        $this->employee_id = $id;

        $this->first_name = $staff->first_name;
        $this->middle_name = $staff->middle_name;
        $this->last_name = $staff->last_name;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->editUrl = route('hr.editstaff', $id);
        $this->leaveslist = Leaves::all();
        $this->listdata();
    }

    public function listdata()
    {
        $this->leaves = Employeeleaves::where('employee_id', $this->employee_id)->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $leave = Employeeleaves::findOrFail($id);
            $this->leave_id = $id;
            $this->start_date = $leave->start_date;
            $this->end_date = $leave->end_date;
            $this->days = $leave->days;
            $this->travel_to = $leave->travel_to;
            $this->othercontact = $leave->othercontact;
            $this->comments = $leave->comments;
            $this->added_by = $leave->added_by;
        } else {
            $this->reset(['start_date', 'end_date', 'days',  'travel_to']);
        }
    }

    public function save()
    {
        $this->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['date', 'after:start_date'],
            'days' => ['required', 'integer'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ]);

        if ($this->modalMode === 'edit' && $this->leave_id) {
            $leave = Employeeleaves::findOrFail($this->leave_id);
            $leave->update(['start_date' => $this->start_date, 'end_date' => $this->end_date, 'days' => $this->days, 'travel_to' => $this->travel_to, 'othercontact' => $this->othercontact, 'comments' => $this->comments, 'added_by' => $this->added_by]);
            $this->listdata();
            session()->flash('success', 'Leave updated successfully!');
        }else {
            Employeeleaves::create([
                'leave_id' => $this->leave_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'days' => $this->days,
                'travel_to' => $this->travel_to,
                'othercontact' => $this->othercontact,
                'status' => $this->status,
                'comments' => $this->comments,
                'added_by' =>Auth::user()->id,
                'employee_id' => $this->employee_id,
            ]);
            session()->flash('success', 'Leave added successfully!');
        }
        $this->showModal = false;
        $this->reset(['start_date', 'end_date', 'days', 'status', 'travel_to', 'othercontact', 'comments']);
    }
    public function delete($uuid)
    {
        $leave = Employeeleaves::findOrFail($uuid);
        $leave->delete();
        $this->listdata();
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
        if (!$this->leave_id) return;

        $employeeId = Auth::id(); // or pass employee_id if admin applies for staff
        $leave = Leaves::find($this->leave_id);

        if (!$leave) {
            $this->available_days = null;
            return;
        }

        $used = EmployeeLeaves::where('employee_id',  $this->employee_id)
                    ->where('leave_id', $this->leave_id)
                    ->sum('days_used');

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
        return view('livewire.hr.staffs.leave');
    }
}

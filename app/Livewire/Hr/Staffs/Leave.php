<?php

namespace App\Livewire\Hr\Staffs;

use App\Livewire\Hr\Leave\Concerns\ShowsLeaveProgress;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Leave extends Component
{
    use ShowsLeaveProgress, WithFileUploads;

    public $search = '';

    public $modalMode = 'create';

    public $showModal = false;

    public $leave_id;

    public $start_date;

    public $end_date;

    public $days;

    public $travel_to;

    public $othercontact;

    public $comments;

    public $added_by;

    public $document;

    public $first_name;

    public $middle_name;

    public $last_name;

    public $gender;

    public $getfullname;

    public $age;

    public $email;

    public $editUrl;

    public $photo;

    public $employee_id;

    public $leaveslist = [];

    /** The leave record open in the edit form; $leave_id is the leave type. */
    public $editingLeaveId = null;

    public $errorMessage;

    public $status = 'Awaiting';

    public $available_days;

    public $selectedLeaveType;

    public $requiresDocument = false;

    public function mount($id = null)
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
        $this->photo = $staff->photo;
        $this->editUrl = route('hr.editstaff', $id);

        // Get leaves filtered by gender and active status
        $employeeGender = $staff->gender;
        $this->leaveslist = Leaves::where('status', 'active')
            ->where(function ($query) use ($employeeGender) {
                $query->whereNull('gender')
                    ->orWhere('gender', '')
                    ->orWhere('gender', 'Both')
                    ->orWhere('gender', $employeeGender);
            })
            ->get();
    }

    /** This employee's leave requests. */
    protected function leaveScope(): Builder
    {
        return Employeeleaves::where('employee_id', $this->employee_id);
    }

    /** Only super admins may change or delete leave records here; everyone else can view them. */
    protected function canManageRecords(): bool
    {
        return (bool) Auth::user()?->isSuperAdmin();
    }

    public function openModal($mode = 'create', $id = null)
    {
        if ($mode === 'edit' && ! $this->canManageRecords()) {
            session()->flash('error', 'Only a super admin can edit leave records.');

            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        $this->editingLeaveId = null;
        if ($mode === 'edit' && $id) {
            $leave = $this->leaveScope()->whereKey($id)->firstOrFail();
            $this->editingLeaveId = $leave->id;
            $this->leave_id = $leave->leave_id;
            $this->start_date = $leave->start_date;
            $this->end_date = $leave->end_date;
            $this->days = $leave->days;
            $this->travel_to = $leave->travel_to;
            $this->othercontact = $leave->othercontact;
            $this->comments = $leave->comments;
            $this->added_by = $leave->added_by;
            $this->checkLeaveRequirements();
        } else {
            $this->reset(['start_date', 'end_date', 'days',  'travel_to', 'document', 'requiresDocument']);
        }
    }

    public function updatedLeaveId()
    {
        $this->checkLeaveRequirements();
        $this->calculateLeaveBalance();
    }

    public function checkLeaveRequirements()
    {
        if ($this->leave_id) {
            $leave = Leaves::find($this->leave_id);
            $this->requiresDocument = $leave ? $leave->require_document : false;
        } else {
            $this->requiresDocument = false;
        }
    }

    public function save()
    {
        $rules = [
            'leave_id' => ['required'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['date', 'after:start_date'],
            'days' => ['required', 'integer'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ];

        // Add document validation if leave type requires it
        if ($this->requiresDocument) {
            $rules['document'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
        }

        $this->validate($rules);

        // Handle document upload
        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('leave-documents', 'public');
        }

        if ($this->modalMode === 'edit' && $this->editingLeaveId) {
            if (! $this->canManageRecords()) {
                session()->flash('error', 'Only a super admin can edit leave records.');
                $this->showModal = false;

                return;
            }

            $leave = $this->leaveScope()->whereKey($this->editingLeaveId)->firstOrFail();
            $updateData = [
                'leave_id' => $this->leave_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'days' => $this->days,
                'travel_to' => $this->travel_to,
                'othercontact' => $this->othercontact,
                'comments' => $this->comments,
                'added_by' => $this->added_by,
            ];

            if ($documentPath) {
                $updateData['document'] = $documentPath;
            }

            $leave->update($updateData);
            session()->flash('success', 'Leave updated successfully!');
        } else {
            Employeeleaves::create([
                'leave_id' => $this->leave_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'days' => $this->days,
                'travel_to' => $this->travel_to,
                'othercontact' => $this->othercontact,
                'status' => $this->status,
                'comments' => $this->comments,
                'document' => $documentPath,
                'added_by' => Auth::user()->id,
                'employee_id' => $this->employee_id,
            ]);
            session()->flash('success', 'Leave added successfully!');
        }
        $this->showModal = false;
        $this->reset(['start_date', 'end_date', 'days', 'status', 'travel_to', 'othercontact', 'comments', 'document', 'editingLeaveId']);
    }

    public function delete($uuid)
    {
        if (! $this->canManageRecords()) {
            session()->flash('error', 'Only a super admin can delete leave records.');

            return;
        }

        $this->leaveScope()->whereKey($uuid)->firstOrFail()->delete();
        session()->flash('success', 'Leave record deleted successfully!');
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
        if (! $this->leave_id) {
            return;
        }

        $leave = Leaves::find($this->leave_id);

        if (! $leave) {
            $this->available_days = null;

            return;
        }

        $used = EmployeeLeaves::where('employee_id', $this->employee_id)
            ->where('leave_id', $this->leave_id)
            ->sum('days');

        $this->available_days = max($leave->days - $used, 0);
    }

    public function calculateEndDate()
    {
        $this->errorMessage = null;

        if ($this->start_date && $this->days && $this->leave_id) {
            $this->calculateLeaveBalance();

            if ($this->available_days <= 0) {
                $this->errorMessage = 'You have no remaining days for this leave type.';
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
        $leaves = $this->withProgress(
            $this->leaveScope()->with($this->progressRelations())->orderBy('created_at', 'desc')->get()
        );

        return view('livewire.hr.staffs.leave', [
            'leaves' => $leaves,
            'progressLeave' => $this->progressLeave($leaves),
            'canManage' => $this->canManageRecords(),
        ]);
    }
}

<?php

namespace App\Livewire\Hr\Leave;

use App\Models\ActingAssignment;
use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use App\Models\LeaveSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Requestleave extends Component
{
    use WithFileUploads;

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

    public $document;

    public $employee;

    public $employee_id;

    public $leaveslist = [];

    public $leaves = [];

    public $errorMessage;

    public $status = 'Awaiting';

    public $available_days;

    public $hasEmployeeRecord = false;

    public $editingLeaveId = null;

    public $requiresDocument = false;

    // Acting Assignment fields
    public $showActingAssignment = false;

    public $actingAssignmentRequired = false;

    public $acting_employee_id;

    public $acting_designation_id;

    public $acting_department_id;

    public $acting_responsibilities;

    public $acting_notes;

    public $notify_acting_employee = true;

    public $grant_system_access = false;

    public $employeesList = [];

    public $designationsList = [];

    public $departmentsList = [];

    // Searchable acting pickers
    public $actingEmployeeSearch = '';

    public $showActingEmployeeDropdown = false;

    public $actingDesignationSearch = '';

    public $showActingDesignationDropdown = false;

    public $actingDepartmentSearch = '';

    public $showActingDepartmentDropdown = false;

    public function mount()
    {
        // Check if logged-in user is connected to an employee record
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->employee_id = $this->employee->id;

            // Get leaves filtered by gender and active status
            $employeeGender = $this->employee->gender;
            $this->leaveslist = Leaves::where('status', 'active')
                ->where(function ($query) use ($employeeGender) {
                    $query->whereNull('gender')
                        ->orWhere('gender', '')
                        ->orWhere('gender', 'Both')
                        ->orWhere('gender', $employeeGender);
                })
                ->get();

            $this->listdata();

            // Load employees, designations, and departments for acting assignment
            $this->employeesList = Employee::where('status', 'active')
                ->where('id', '!=', $this->employee_id)
                ->get();
            $this->designationsList = designations::where('status', 'active')->get();
            $this->departmentsList = departments::all();
        }
    }

    public function listdata()
    {
        if (! $this->hasEmployeeRecord) {
            return;
        }

        $this->leaves = Employeeleaves::where('employee_id', $this->employee_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        if (! $this->hasEmployeeRecord) {
            return;
        }

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
            $this->checkLeaveRequirements();
        } else {
            $this->editingLeaveId = null;
            $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments', 'document', 'requiresDocument']);
        }
    }

    public function updatedLeaveId()
    {
        $this->checkLeaveRequirements();
        $this->calculateLeaveBalance();
        $this->checkActingAssignmentRequirement();
    }

    public function updatedDays()
    {
        $this->calculateEndDate();
        $this->checkActingAssignmentRequirement();
    }

    public function checkLeaveRequirements()
    {
        if ($this->leave_id) {
            $leave = Leaves::find($this->leave_id);
            $this->requiresDocument = $leave ? (bool) $leave->require_document : false;
        } else {
            $this->requiresDocument = false;
        }
    }

    public function checkActingAssignmentRequirement()
    {
        $actingEnabled = LeaveSetting::get('acting_assignment_enabled', true);
        $actingMandatory = LeaveSetting::get('acting_assignment_mandatory', false);
        $minDays = LeaveSetting::get('acting_assignment_min_days', 5);

        if (! $actingEnabled) {
            $this->showActingAssignment = false;
            $this->actingAssignmentRequired = false;

            return;
        }

        if ($this->days && $this->days >= $minDays) {
            $this->showActingAssignment = true;
            $this->actingAssignmentRequired = $actingMandatory;
        } else {
            $this->showActingAssignment = false;
            $this->actingAssignmentRequired = false;
        }
    }

    public function getFilteredActingEmployeesProperty()
    {
        $term = trim($this->actingEmployeeSearch);

        return collect($this->employeesList)->filter(function ($emp) use ($term) {
            if ($term === '') {
                return true;
            }

            $haystack = strtolower(
                ($emp->first_name ?? '').' '.
                ($emp->last_name ?? '').' '.
                ($emp->employee_no ?? '').' '.
                ($emp->designation->name ?? '')
            );

            return str_contains($haystack, strtolower($term));
        })->take(50)->values();
    }

    public function getSelectedActingEmployeeProperty()
    {
        if (! $this->acting_employee_id) {
            return null;
        }

        return collect($this->employeesList)->firstWhere('id', $this->acting_employee_id);
    }

    public function selectActingEmployee($id)
    {
        $this->acting_employee_id = $id;
        $this->showActingEmployeeDropdown = false;
        $this->actingEmployeeSearch = '';
    }

    public function clearActingEmployee()
    {
        $this->acting_employee_id = null;
        $this->actingEmployeeSearch = '';
        $this->showActingEmployeeDropdown = true;
    }

    public function getFilteredActingDesignationsProperty()
    {
        $term = trim($this->actingDesignationSearch);

        return collect($this->designationsList)->filter(function ($d) use ($term) {
            if ($term === '') {
                return true;
            }

            return str_contains(strtolower($d->name ?? ''), strtolower($term));
        })->take(50)->values();
    }

    public function getSelectedActingDesignationProperty()
    {
        if (! $this->acting_designation_id) {
            return null;
        }

        return collect($this->designationsList)->firstWhere('id', $this->acting_designation_id);
    }

    public function selectActingDesignation($id)
    {
        $this->acting_designation_id = $id;
        $this->showActingDesignationDropdown = false;
        $this->actingDesignationSearch = '';
    }

    public function clearActingDesignation()
    {
        $this->acting_designation_id = null;
        $this->actingDesignationSearch = '';
        $this->showActingDesignationDropdown = true;
    }

    public function getFilteredActingDepartmentsProperty()
    {
        $term = trim($this->actingDepartmentSearch);

        return collect($this->departmentsList)->filter(function ($d) use ($term) {
            if ($term === '') {
                return true;
            }

            return str_contains(strtolower($d->name ?? ''), strtolower($term));
        })->take(50)->values();
    }

    public function getSelectedActingDepartmentProperty()
    {
        if (! $this->acting_department_id) {
            return null;
        }

        return collect($this->departmentsList)->firstWhere('id', $this->acting_department_id);
    }

    public function selectActingDepartment($id)
    {
        $this->acting_department_id = $id;
        $this->showActingDepartmentDropdown = false;
        $this->actingDepartmentSearch = '';
    }

    public function clearActingDepartment()
    {
        $this->acting_department_id = null;
        $this->actingDepartmentSearch = '';
        $this->showActingDepartmentDropdown = true;
    }

    public function save()
    {
        if (! $this->hasEmployeeRecord) {
            return;
        }

        $rules = [
            'leave_id' => ['required'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'days' => ['required', 'integer', 'min:1'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ];

        // Add document validation if leave type requires it
        if ($this->requiresDocument) {
            $rules['document'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
        }

        // Add acting assignment validation if required
        if ($this->actingAssignmentRequired && $this->showActingAssignment) {
            $rules['acting_employee_id'] = ['required', 'exists:employees,id'];
        }

        $this->validate($rules);

        // Handle document upload
        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('leave-documents', 'public');
        }

        $leaveRequest = Employeeleaves::create([
            'leave_id' => $this->leave_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $this->days,
            'travel_to' => $this->travel_to,
            'othercontact' => $this->othercontact,
            'status' => $this->status,
            'comments' => $this->comments,
            'document' => $documentPath,
            'added_by' => Auth::id(),
            'employee_id' => $this->employee_id,
        ]);

        // Create acting assignment if provided
        if ($this->acting_employee_id && $this->showActingAssignment) {
            ActingAssignment::create([
                'leave_request_id' => $leaveRequest->id,
                'employee_on_leave_id' => $this->employee_id,
                'acting_employee_id' => $this->acting_employee_id,
                'acting_designation_id' => $this->acting_designation_id,
                'acting_department_id' => $this->acting_department_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'responsibilities' => $this->acting_responsibilities,
                'notes' => $this->acting_notes,
                'notify_acting_employee' => $this->notify_acting_employee,
                'grant_system_access' => $this->grant_system_access,
                'added_by' => Auth::id(),
            ]);
        }

        session()->flash('success', 'Leave request submitted successfully!');
        $this->showModal = false;
        $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments', 'document',
            'acting_employee_id', 'acting_designation_id', 'acting_department_id', 'acting_responsibilities', 'acting_notes',
            'actingEmployeeSearch', 'actingDesignationSearch', 'actingDepartmentSearch',
            'showActingEmployeeDropdown', 'showActingDesignationDropdown', 'showActingDepartmentDropdown']);
        $this->listdata();
    }

    public function update()
    {
        if (! $this->hasEmployeeRecord || ! $this->editingLeaveId) {
            return;
        }

        $rules = [
            'leave_id' => ['required'],
            'start_date' => ['required', 'date'],
            'days' => ['required', 'integer', 'min:1'],
            'travel_to' => ['required', 'string', 'max:255'],
            'othercontact' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
        ];

        // Add document validation if leave type requires it
        if ($this->requiresDocument) {
            $rules['document'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
        }

        $this->validate($rules);

        $leave = Employeeleaves::where('id', $this->editingLeaveId)
            ->where('employee_id', $this->employee_id)
            ->firstOrFail();

        $updateData = [
            'leave_id' => $this->leave_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $this->days,
            'travel_to' => $this->travel_to,
            'othercontact' => $this->othercontact,
            'comments' => $this->comments,
        ];

        // Handle document upload
        if ($this->document) {
            $updateData['document'] = $this->document->store('leave-documents', 'public');
        }

        $leave->update($updateData);

        session()->flash('success', 'Leave request updated successfully!');
        $this->showModal = false;
        $this->reset(['leave_id', 'start_date', 'end_date', 'days', 'travel_to', 'othercontact', 'comments', 'document', 'editingLeaveId']);
        $this->listdata();
    }

    public function delete($uuid)
    {
        if (! $this->hasEmployeeRecord) {
            return;
        }

        $leave = Employeeleaves::where('id', $uuid)
            ->where('employee_id', $this->employee_id)
            ->firstOrFail();

        // Employees may only withdraw requests that are still pending.
        if ($leave->status !== 'Awaiting') {
            session()->flash('error', 'You can only delete leave requests that are still awaiting approval.');

            return;
        }

        $leave->delete();
        $this->listdata();
        session()->flash('success', 'Leave request deleted successfully!');
    }

    public function updatedStartDate()
    {
        $this->calculateLeaveBalance();
        $this->calculateEndDate();
    }

    public function calculateLeaveBalance()
    {
        if (! $this->leave_id || ! $this->hasEmployeeRecord) {
            return;
        }

        $leave = Leaves::find($this->leave_id);

        if (! $leave) {
            $this->available_days = null;

            return;
        }

        $year = $this->start_date
            ? Carbon::parse($this->start_date)->year
            : now()->year;

        $used = Employeeleaves::where('employee_id', $this->employee_id)
            ->where('leave_id', $this->leave_id)
            ->where('status', 'Approved')
            ->where(function ($q) use ($year) {
                $q->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            })
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
            $this->end_date = $start->copy()->addDays((int) $this->days)->toDateString();
        } else {
            $this->end_date = null;
        }
    }

    public function render()
    {
        return view('livewire.hr.leave.requestleave');
    }
}

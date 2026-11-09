<?php

namespace App\Livewire\Hr\Leave;

use App\Livewire\Hr\Leave\Concerns\ShowsLeaveProgress;
use App\Models\ActingAssignment;
use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use App\Models\LeaveSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Requestleave extends Component
{
    use ShowsLeaveProgress, WithFileUploads, WithPagination;

    /** Status tabs: key => stored statuses it covers (lower-cased). An empty list means every request. */
    public const STATUS_TABS = [
        '' => ['label' => 'All', 'statuses' => []],
        'awaiting' => ['label' => 'Awaiting approval', 'statuses' => ['awaiting', 'pending']],
        'active' => ['label' => 'In progress', 'statuses' => ['active']],
        'approved' => ['label' => 'Approved', 'statuses' => ['approved']],
        'rejected' => ['label' => 'Rejected', 'statuses' => ['rejected']],
    ];

    /** Columns the table can sort by: query string key => database column. */
    protected const SORTABLE = [
        'created_at' => 'created_at',
        'start_date' => 'start_date',
        'end_date' => 'end_date',
        'days' => 'days',
        'status' => 'status',
    ];

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    // Datatable controls
    public $statusFilter = '';

    /** Rows per page; 'all' shows every request in one scrollable table. */
    public $perPage = '25';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

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

            // Load employees, designations, and departments for acting assignment
            $this->employeesList = Employee::where('status', 'active')
                ->where('id', '!=', $this->employee_id)
                ->get();
            $this->designationsList = designations::where('status', 'active')->get();
            $this->departmentsList = departments::all();
        }
    }

    /** The employee's own requests, and nobody else's. */
    protected function leaveScope(): Builder
    {
        return Employeeleaves::where('employee_id', $this->employee_id);
    }

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, self::SORTABLE)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /** The employee's own requests matching the search box, before the status tab is applied. */
    protected function searchedLeaves(): Builder
    {
        $term = trim($this->search);

        return $this->leaveScope()
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('travel_to', 'like', "%{$term}%")
                ->orWhere('status', 'like', "%{$term}%")
                ->orWhereHas('leave', fn ($leave) => $leave->where('name', 'like', "%{$term}%"))));
    }

    /** Count per status tab, for the tab badges. */
    protected function statusCounts(): array
    {
        $byStatus = $this->searchedLeaves()
            ->selectRaw('LOWER(status) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        return collect(self::STATUS_TABS)->map(fn ($tab) => $tab['statuses'] === []
            ? (int) $byStatus->sum()
            : (int) collect($tab['statuses'])->sum(fn ($status) => $byStatus[$status] ?? 0))->all();
    }

    /** One page of the employee's requests, filtered and sorted as the table controls say. */
    protected function myLeaves()
    {
        $statuses = self::STATUS_TABS[$this->statusFilter]['statuses'] ?? [];
        $column = self::SORTABLE[$this->sortField] ?? 'created_at';
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query = $this->searchedLeaves()
            ->with($this->progressRelations())
            ->when($statuses !== [], fn ($q) => $q->whereIn(DB::raw('LOWER(status)'), $statuses))
            // days is stored as text; "+ 0" sorts it as a number on both MySQL and SQLite
            ->when($column === 'days', fn ($q) => $q->orderByRaw("days + 0 {$direction}"), fn ($q) => $q->orderBy($column, $direction))
            ->orderBy('created_at', 'desc');

        $perPage = $this->perPage === 'all' ? max($query->count(), 1) : max(10, min((int) $this->perPage, 100));

        return $query->paginate($perPage);
    }

    protected function ownLeave($id): Employeeleaves
    {
        return $this->leaveScope()->whereKey($id)->firstOrFail();
    }

    /**
     * A request can be changed or withdrawn only before any approver has acted on it.
     */
    public static function isEditable(Employeeleaves $leave): bool
    {
        return strtolower((string) $leave->status) === 'awaiting'
            && ($leave->relationLoaded('approvalnote') ? $leave->approvalnote->isEmpty() : ! $leave->approvalnote()->exists());
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
            $leave = $this->ownLeave($id);

            if (! static::isEditable($leave)) {
                $this->showModal = false;
                session()->flash('error', 'This request is already being processed and can no longer be edited.');

                return;
            }

            $this->editingLeaveId = $leave->id;
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

        $leave = $this->ownLeave($this->editingLeaveId);

        if (! static::isEditable($leave)) {
            session()->flash('error', 'This request is already being processed and can no longer be edited.');
            $this->showModal = false;

            return;
        }

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
    }

    public function delete($uuid)
    {
        if (! $this->hasEmployeeRecord) {
            return;
        }

        $leave = $this->ownLeave($uuid);

        // Employees may only withdraw requests no approver has acted on yet
        if (! static::isEditable($leave)) {
            session()->flash('error', 'You can only delete leave requests that no approver has acted on yet.');

            return;
        }

        $leave->delete();
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
        if (! $this->hasEmployeeRecord) {
            return view('livewire.hr.leave.requestleave', ['leaves' => null, 'progressLeave' => null, 'statusCounts' => []]);
        }

        // Progress is worked out for the rows on screen only
        $leaves = $this->myLeaves();
        $this->withProgress($leaves->getCollection())
            ->each(fn (Employeeleaves $leave) => $leave->editable = static::isEditable($leave));

        return view('livewire.hr.leave.requestleave', [
            'leaves' => $leaves,
            'progressLeave' => $this->progressLeave($leaves->getCollection()),
            'statusCounts' => $this->statusCounts(),
        ]);
    }
}

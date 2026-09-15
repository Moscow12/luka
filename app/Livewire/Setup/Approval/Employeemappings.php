<?php

namespace App\Livewire\Setup\Approval;

use App\Models\approvallevel;
use App\Models\approvalleveltoemployee;
use App\Models\departments;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Employeemappings extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $approval_level_employee_id;

    public $modalMode = 'create';

    public $showModal = false;

    public $approval_level_id;

    public $employee_id;

    public $department_ids = [];

    public $is_active = true;

    public $approvallevels = [];

    public $employees = [];

    public $departments = [];

    // Searchable employee picker (inside the modal)
    public $employeeSearch = '';

    public $showEmployeeDropdown = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getFilteredEmployeesProperty()
    {
        $term = trim($this->employeeSearch);

        return $this->employees->filter(function ($emp) use ($term) {
            if ($term === '') {
                return true;
            }

            $haystack = strtolower($emp->getFullName().' '.$emp->employee_no);

            return str_contains($haystack, strtolower($term));
        })->take(50);
    }

    public function getSelectedEmployeeProperty()
    {
        if (! $this->employee_id) {
            return null;
        }

        return $this->employees->firstWhere('id', $this->employee_id);
    }

    public function selectEmployee($id)
    {
        $this->employee_id = $id;
        $this->showEmployeeDropdown = false;
        $this->employeeSearch = '';
    }

    public function clearEmployee()
    {
        $this->employee_id = null;
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = true;
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        $this->employeeSearch = '';
        $this->showEmployeeDropdown = false;

        if ($mode === 'edit' && $id) {
            $emp = approvalleveltoemployee::with('departments:id')->findOrFail($id);
            $this->approval_level_employee_id = $id;
            $this->approval_level_id = $emp->approval_level_id;
            $this->employee_id = $emp->employee_id;
            $this->department_ids = $emp->departments->pluck('id')->toArray();
            $this->is_active = $emp->is_active;
        } else {
            $this->reset(['approval_level_employee_id', 'approval_level_id', 'employee_id', 'department_ids']);
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'approval_level_id' => ['required', 'exists:approvallevels,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'department_ids' => ['array'],
            'department_ids.*' => ['exists:departments,id'],
            'is_active' => ['boolean'],
        ]);

        if ($this->modalMode === 'edit' && $this->approval_level_employee_id) {
            $emp = approvalleveltoemployee::findOrFail($this->approval_level_employee_id);
            $emp->update([
                'approval_level_id' => $this->approval_level_id,
                'employee_id' => $this->employee_id,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Employee approval mapping updated successfully!');
        } else {
            $emp = approvalleveltoemployee::create([
                'approval_level_id' => $this->approval_level_id,
                'employee_id' => $this->employee_id,
                'is_active' => $this->is_active,
                'added_by' => Auth::user()->id,
            ]);
            session()->flash('success', 'Employee approval mapping added successfully!');
        }

        $emp->departments()->sync($this->department_ids);

        $this->showModal = false;
        $this->reset(['approval_level_employee_id', 'approval_level_id', 'employee_id', 'department_ids']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $emp = approvalleveltoemployee::findOrFail($uuid);
        $emp->delete();
        session()->flash('success', 'Employee approval mapping deleted successfully!');
    }

    public function mount()
    {
        $this->approvallevels = approvallevel::where('is_active', true)->orderBy('level_order')->get();
        $this->employees = Employee::where('status', 'Active')->get();
        $this->departments = departments::where('status', 'active')->orderBy('name')->get();
    }

    public function render()
    {
        $employeeMappings = approvalleveltoemployee::with(['approval_level.approvalleveltodocuments', 'employee', 'departments'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                })->orWhereHas('approval_level', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->paginate(10);

        return view('livewire.setup.approval.employeemappings', ['employeeMappings' => $employeeMappings]);
    }
}

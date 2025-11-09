<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\workstations;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Stafflist extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $department = '';

    #[Url]
    public $designation = '';

    #[Url]
    public $workstation = '';

    #[Url]
    public $status = '';

    #[Url]
    public $gender = '';

    #[Url]
    public $employmentType = '';

    public $perPage = 10;

    public $showFilters = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
    }

    public function updatingDesignation()
    {
        $this->resetPage();
    }

    public function updatingWorkstation()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingGender()
    {
        $this->resetPage();
    }

    public function updatingEmploymentType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'department',
            'designation',
            'workstation',
            'status',
            'gender',
            'employmentType',
        ]);
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function render()
    {
        $employees = Employee::query()
            ->with(['department', 'designation', 'workstation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->department, fn ($query) => $query->where('department_id', $this->department))
            ->when($this->designation, fn ($query) => $query->where('designation_id', $this->designation))
            ->when($this->workstation, fn ($query) => $query->where('workstation_id', $this->workstation))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->gender, fn ($query) => $query->where('gender', $this->gender))
            ->when($this->employmentType, fn ($query) => $query->where('employment_type', $this->employmentType))
            ->latest()
            ->paginate($this->perPage);

        $departments = departments::where('status', 'active')->get();
        $designations = designations::where('status', 'Active')->get();
        $workstations = workstations::all();

        return view('livewire.hr.staffs.stafflist', [
            'employees' => $employees,
            'departments' => $departments,
            'designations' => $designations,
            'workstations' => $workstations,
        ]);
    }
}

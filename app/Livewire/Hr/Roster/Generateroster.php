<?php

namespace App\Livewire\Hr\Roster;

use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeroster;
use App\Models\shifts;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Generateroster extends Component
{
    use WithPagination;

    // Filters
    #[Url]
    public $department = '';

    #[Url]
    public $search = '';

    public $status = 'Active';

    // Roster Generation Data
    public $selectedEmployees = [];

    public $shift = '';

    public $rosterDate = '';

    public $shiftType = 'regular';

    public $notes = '';

    public $selectAll = false;

    // UI State
    public $showFilters = false;

    public $perPage = 20;

    public function mount()
    {
        // Set default roster date to today
        $this->rosterDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
        $this->selectedEmployees = [];
        $this->selectAll = false;
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedEmployees = $this->getEmployeesQuery()->pluck('id')->toArray();
        } else {
            $this->selectedEmployees = [];
        }
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'department',
            'status',
        ]);
        $this->selectedEmployees = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function generateRoster()
    {
        // Validation
        $this->validate([
            'selectedEmployees' => 'required|array|min:1',
            'shift' => 'required|exists:shifts,id',
            'rosterDate' => 'required|date',
            'shiftType' => 'required|in:regular,overtime,special',
        ], [
            'selectedEmployees.required' => 'Please select at least one employee',
            'selectedEmployees.min' => 'Please select at least one employee',
            'shift.required' => 'Please select a shift',
            'rosterDate.required' => 'Please select a roster date',
        ]);

        $created = 0;
        $errors = [];

        foreach ($this->selectedEmployees as $employeeId) {
            try {
                $employee = Employee::find($employeeId);

                // Check if roster already exists
                $existing = employeeroster::where('employee_id', $employeeId)
                    ->where('roster_date', $this->rosterDate)
                    ->first();

                if ($existing) {
                    $errors[] = "{$employee->first_name} {$employee->last_name} already has a roster for this date";

                    continue;
                }

                employeeroster::create([
                    'employee_id' => $employeeId,
                    'department_id' => $employee->department_id,
                    'shift_id' => $this->shift,
                    'roster_date' => $this->rosterDate,
                    'shift_type' => $this->shiftType,
                    'status' => 'scheduled',
                    'notes' => $this->notes,
                    'added_by' => Auth::id(),
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Error creating roster for employee ID {$employeeId}: ".$e->getMessage();
            }
        }

        // Show success/error messages
        if ($created > 0) {
            session()->flash('success', "Successfully created {$created} roster(s)");
        }

        if (count($errors) > 0) {
            session()->flash('errors', $errors);
        }

        // Reset selection
        $this->reset(['selectedEmployees', 'selectAll', 'notes']);
    }

    private function getEmployeesQuery()
    {
        return Employee::query()
            ->with(['department', 'designation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->department, fn ($query) => $query->where('department_id', $this->department))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest();
    }

    public function render()
    {
        $employees = $this->getEmployeesQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();
        $shifts = shifts::where('status', 'active')->orderBy('name')->get();

        return view('livewire.hr.roster.generateroster', [
            'employees' => $employees,
            'departments' => $departments,
            'shifts' => $shifts,
        ]);
    }
}

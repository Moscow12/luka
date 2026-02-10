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

    // Auto-Generate Properties
    public $autoGenerateMode = false;

    public $startDate = '';

    public $endDate = '';

    public $shiftCapacities = [];

    public $distributionMethod = 'round_robin';

    public function mount()
    {
        // Set default roster date to today
        $this->rosterDate = now()->format('Y-m-d');
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(6)->format('Y-m-d');
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

    public function toggleAutoGenerateMode()
    {
        $this->autoGenerateMode = ! $this->autoGenerateMode;
        $this->reset(['notes']);
    }

    public function autoGenerateRoster()
    {
        // Validation
        $this->validate([
            'selectedEmployees' => 'required|array|min:1',
            'startDate' => 'required|date|after_or_equal:today',
            'endDate' => 'required|date|after_or_equal:startDate',
            'shiftCapacities' => 'required|array|min:1',
            'distributionMethod' => 'required|in:round_robin,random,balanced',
        ], [
            'selectedEmployees.required' => 'Please select at least one employee',
            'selectedEmployees.min' => 'Please select at least one employee',
            'startDate.required' => 'Please select a start date',
            'startDate.after_or_equal' => 'Start date must be today or later',
            'endDate.required' => 'Please select an end date',
            'endDate.after_or_equal' => 'End date must be on or after start date',
            'shiftCapacities.required' => 'Please set capacity for at least one shift',
            'shiftCapacities.min' => 'Please set capacity for at least one shift',
        ]);

        // Validate shift capacities
        $hasCapacity = false;
        foreach ($this->shiftCapacities as $capacity) {
            if ($capacity > 0) {
                $hasCapacity = true;
                break;
            }
        }

        if (! $hasCapacity) {
            session()->flash('error', 'Please set capacity greater than 0 for at least one shift');

            return;
        }

        // Validate date range (max 31 days)
        $start = \Carbon\Carbon::parse($this->startDate);
        $end = \Carbon\Carbon::parse($this->endDate);
        $daysDiff = $start->diffInDays($end) + 1;

        if ($daysDiff > 31) {
            session()->flash('error', 'Date range cannot exceed 31 days');

            return;
        }

        try {
            \DB::beginTransaction();

            $created = 0;
            $skipped = 0;
            $errors = [];

            // Get selected employees
            $employees = Employee::whereIn('id', $this->selectedEmployees)->get();
            $employeePool = $employees->toArray();
            $employeeIndex = 0;

            // Generate rosters for each date
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                foreach ($this->shiftCapacities as $shiftId => $capacity) {
                    if ($capacity <= 0) {
                        continue;
                    }

                    $assignedCount = 0;
                    $attempts = 0;
                    $maxAttempts = count($employeePool) * 2;

                    while ($assignedCount < $capacity && $attempts < $maxAttempts) {
                        $employee = $employeePool[$employeeIndex % count($employeePool)];
                        $employeeId = $employee['id'];

                        // Check if employee already has a roster for this date
                        $hasConflict = employeeroster::where('employee_id', $employeeId)
                            ->where('roster_date', $currentDate->format('Y-m-d'))
                            ->exists();

                        if (! $hasConflict) {
                            // Create roster
                            employeeroster::create([
                                'employee_id' => $employeeId,
                                'department_id' => $employee['department_id'],
                                'shift_id' => $shiftId,
                                'roster_date' => $currentDate->format('Y-m-d'),
                                'shift_type' => $this->shiftType,
                                'status' => 'scheduled',
                                'notes' => 'Auto-generated ('.$this->distributionMethod.')',
                                'added_by' => Auth::id(),
                            ]);

                            $created++;
                            $assignedCount++;
                        } else {
                            $skipped++;
                        }

                        $employeeIndex++;
                        $attempts++;
                    }

                    // If couldn't fill the capacity, note it
                    if ($assignedCount < $capacity) {
                        $shift = shifts::find($shiftId);
                        $errors[] = "Could not fill {$shift->name} on {$currentDate->format('Y-m-d')} - Only {$assignedCount}/{$capacity} assigned";
                    }
                }

                $currentDate->addDay();
            }

            \DB::commit();

            // Show success/warning messages
            $message = "Successfully created {$created} roster(s) for {$daysDiff} day(s)";
            if ($skipped > 0) {
                $message .= " ({$skipped} skipped due to conflicts)";
            }

            session()->flash('success', $message);

            if (count($errors) > 0) {
                session()->flash('errors', $errors);
            }

            // Reset
            $this->reset(['selectedEmployees', 'selectAll', 'shiftCapacities']);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error generating rosters: '.$e->getMessage());
        }
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

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

    /**
     * Check if employee worked night shift yesterday and needs rest
     */
    private function needsRestAfterNightShift($employeeId, $currentDate)
    {
        $yesterday = \Carbon\Carbon::parse($currentDate)->subDay();

        // Get yesterday's roster
        $yesterdayRoster = employeeroster::where('employee_id', $employeeId)
            ->where('roster_date', $yesterday->format('Y-m-d'))
            ->with('shift')
            ->first();

        if (! $yesterdayRoster || ! $yesterdayRoster->shift) {
            return false;
        }

        // Check if it was a night shift (shift ending after midnight or starting after 10 PM)
        $shiftEndTime = \Carbon\Carbon::parse($yesterdayRoster->shift->end_time);
        $shiftStartTime = \Carbon\Carbon::parse($yesterdayRoster->shift->start_time);

        // Night shift typically starts after 8 PM or ends before 8 AM
        $isNightShift = $shiftStartTime->hour >= 20 || $shiftEndTime->hour <= 8;

        return $isNightShift;
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

            // Get active shifts for rotation
            $activeShifts = array_keys(array_filter($this->shiftCapacities, fn ($capacity) => $capacity > 0));

            // Track employee shift assignments for balanced distribution
            $employeeShiftTracker = [];
            foreach ($employeePool as $employee) {
                $employeeShiftTracker[$employee['id']] = [
                    'last_shift_id' => null,
                    'shift_counts' => [],
                    'days_worked' => 0,
                ];
            }

            if ($this->distributionMethod === 'round_robin') {
                // Round Robin: Rotate employees through shifts fairly
                $employeeIndex = 0;

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

                            // Check if employee needs rest after night shift
                            $needsRest = $this->needsRestAfterNightShift($employeeId, $currentDate);

                            // Check if employee already has a roster for this date
                            $hasConflict = employeeroster::where('employee_id', $employeeId)
                                ->where('roster_date', $currentDate->format('Y-m-d'))
                                ->exists();

                            if (! $hasConflict && ! $needsRest) {
                                employeeroster::create([
                                    'employee_id' => $employeeId,
                                    'department_id' => $employee['department_id'],
                                    'shift_id' => $shiftId,
                                    'roster_date' => $currentDate->format('Y-m-d'),
                                    'shift_type' => $this->shiftType,
                                    'status' => 'scheduled',
                                    'notes' => 'Auto-generated (round_robin)',
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

                        if ($assignedCount < $capacity) {
                            $shift = shifts::find($shiftId);
                            $errors[] = "Could not fill {$shift->name} on {$currentDate->format('Y-m-d')} - Only {$assignedCount}/{$capacity} assigned";
                        }
                    }

                    $currentDate->addDay();
                }
            } elseif ($this->distributionMethod === 'balanced') {
                // Balanced: Rotate shifts for each employee across the period
                $employeeIndex = 0;
                $shiftRotationPattern = $activeShifts; // Shifts to rotate through

                $currentDate = $start->copy();
                while ($currentDate->lte($end)) {
                    // Calculate total staff needed for this day
                    $totalStaffNeeded = array_sum($this->shiftCapacities);
                    $staffAssigned = 0;

                    // Assign employees to shifts for this day
                    $attempts = 0;
                    $maxAttempts = count($employeePool) * 3;

                    while ($staffAssigned < $totalStaffNeeded && $attempts < $maxAttempts) {
                        $employee = $employeePool[$employeeIndex % count($employeePool)];
                        $employeeId = $employee['id'];

                        // Check if employee needs rest after night shift
                        $needsRest = $this->needsRestAfterNightShift($employeeId, $currentDate);

                        // Check if employee already has a roster for this date
                        $hasConflict = employeeroster::where('employee_id', $employeeId)
                            ->where('roster_date', $currentDate->format('Y-m-d'))
                            ->exists();

                        if (! $hasConflict && ! $needsRest) {
                            // Determine which shift to assign based on rotation
                            $lastShift = $employeeShiftTracker[$employeeId]['last_shift_id'];
                            $daysWorked = $employeeShiftTracker[$employeeId]['days_worked'];

                            // Create rotation pattern: work 2-3 days, then potentially off
                            // Rotate through available shifts
                            $assignedShift = null;

                            foreach ($this->shiftCapacities as $shiftId => $capacity) {
                                if ($capacity <= 0) {
                                    continue;
                                }

                                // Get current count for this shift on this day
                                $currentShiftCount = employeeroster::where('shift_id', $shiftId)
                                    ->where('roster_date', $currentDate->format('Y-m-d'))
                                    ->count();

                                // Check if this shift still needs staff
                                if ($currentShiftCount < $capacity) {
                                    // Prefer different shift than last assigned
                                    if ($shiftId != $lastShift || count($activeShifts) === 1) {
                                        $assignedShift = $shiftId;
                                        break;
                                    }
                                }
                            }

                            // If we found a shift, assign it
                            if ($assignedShift) {
                                employeeroster::create([
                                    'employee_id' => $employeeId,
                                    'department_id' => $employee['department_id'],
                                    'shift_id' => $assignedShift,
                                    'roster_date' => $currentDate->format('Y-m-d'),
                                    'shift_type' => $this->shiftType,
                                    'status' => 'scheduled',
                                    'notes' => 'Auto-generated (balanced)',
                                    'added_by' => Auth::id(),
                                ]);

                                // Update tracker
                                $employeeShiftTracker[$employeeId]['last_shift_id'] = $assignedShift;
                                $employeeShiftTracker[$employeeId]['days_worked']++;
                                $employeeShiftTracker[$employeeId]['shift_counts'][$assignedShift] =
                                    ($employeeShiftTracker[$employeeId]['shift_counts'][$assignedShift] ?? 0) + 1;

                                $created++;
                                $staffAssigned++;
                            }
                        } else {
                            $skipped++;
                        }

                        $employeeIndex++;
                        $attempts++;
                    }

                    $currentDate->addDay();
                }
            } elseif ($this->distributionMethod === 'random') {
                // Random: Randomly assign employees to shifts with rotation
                $currentDate = $start->copy();

                while ($currentDate->lte($end)) {
                    // Shuffle employees for this day
                    $shuffledEmployees = $employeePool;
                    shuffle($shuffledEmployees);

                    $employeeIndex = 0;

                    // For each shift that needs staff
                    foreach ($this->shiftCapacities as $shiftId => $capacity) {
                        if ($capacity <= 0) {
                            continue;
                        }

                        $assignedCount = 0;
                        $attempts = 0;
                        $maxAttempts = count($shuffledEmployees) * 2;

                        while ($assignedCount < $capacity && $attempts < $maxAttempts) {
                            $employee = $shuffledEmployees[$employeeIndex % count($shuffledEmployees)];
                            $employeeId = $employee['id'];

                            // Check if employee needs rest after night shift
                            $needsRest = $this->needsRestAfterNightShift($employeeId, $currentDate);

                            // Check conflicts
                            $hasConflict = employeeroster::where('employee_id', $employeeId)
                                ->where('roster_date', $currentDate->format('Y-m-d'))
                                ->exists();

                            // Check if employee worked this shift too recently (within last 2 days)
                            $recentShift = employeeroster::where('employee_id', $employeeId)
                                ->where('shift_id', $shiftId)
                                ->whereBetween('roster_date', [
                                    $currentDate->copy()->subDays(2)->format('Y-m-d'),
                                    $currentDate->copy()->subDay()->format('Y-m-d'),
                                ])
                                ->exists();

                            if (! $hasConflict && ! $recentShift && ! $needsRest) {
                                employeeroster::create([
                                    'employee_id' => $employeeId,
                                    'department_id' => $employee['department_id'],
                                    'shift_id' => $shiftId,
                                    'roster_date' => $currentDate->format('Y-m-d'),
                                    'shift_type' => $this->shiftType,
                                    'status' => 'scheduled',
                                    'notes' => 'Auto-generated (random with rotation)',
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

                        if ($assignedCount < $capacity) {
                            $shift = shifts::find($shiftId);
                            $errors[] = "Could not fill {$shift->name} on {$currentDate->format('Y-m-d')} - Only {$assignedCount}/{$capacity} assigned";
                        }
                    }

                    $currentDate->addDay();
                }
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

<?php

namespace App\Livewire\Hr\Roster;

use App\Exports\RosterExport;
use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeroster;
use App\Models\shifts;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Viewroster extends Component
{
    // Filters
    #[Url]
    public $selectedMonth;

    #[Url]
    public $selectedYear;

    #[Url]
    public $department = '';

    #[Url]
    public $search = '';

    // UI State
    public $showLegend = true;

    // Modal state for editing
    public $editingCell = null;

    public $editShiftId = '';

    public $editStatus = '';

    public $editNotes = '';

    public function mount()
    {
        // Set default to current month and year
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->format('m');
        $this->selectedYear = $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->format('m');
        $this->selectedYear = $date->format('Y');
    }

    public function copyPreviousMonth()
    {
        $currentDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $previousDate = $currentDate->copy()->subMonth();

        // Get all rosters from previous month
        $previousRosters = employeeroster::whereYear('roster_date', $previousDate->year)
            ->whereMonth('roster_date', $previousDate->month)
            ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
            ->get();

        if ($previousRosters->isEmpty()) {
            session()->flash('error', 'No rosters found in previous month to copy.');

            return;
        }

        $copied = 0;
        foreach ($previousRosters as $roster) {
            // Calculate the corresponding date in current month
            $day = Carbon::parse($roster->roster_date)->day;
            $daysInCurrentMonth = $currentDate->daysInMonth;

            // Skip if day doesn't exist in current month (e.g., 31st when current month has 30 days)
            if ($day > $daysInCurrentMonth) {
                continue;
            }

            $newDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, $day);

            // Check if roster already exists
            $exists = employeeroster::where('employee_id', $roster->employee_id)
                ->whereDate('roster_date', $newDate->format('Y-m-d'))
                ->exists();

            if (! $exists) {
                employeeroster::create([
                    'employee_id' => $roster->employee_id,
                    'roster_date' => $newDate->format('Y-m-d'),
                    'shift_id' => $roster->shift_id,
                    'shift_type' => $roster->shift_type,
                    'status' => 'scheduled',
                    'department_id' => $roster->department_id,
                    'notes' => null,
                ]);
                $copied++;
            }
        }

        session()->flash('success', "Successfully copied {$copied} roster entries from previous month.");
    }

    public function editCell($employeeId, $date)
    {
        $this->editingCell = $employeeId.'_'.$date;

        // Load existing roster data if exists
        $roster = employeeroster::where('employee_id', $employeeId)
            ->whereDate('roster_date', $date)
            ->first();

        if ($roster) {
            $this->editShiftId = $roster->shift_id;
            $this->editStatus = $roster->status;
            $this->editNotes = $roster->notes;
        } else {
            $this->editShiftId = '';
            $this->editStatus = 'scheduled';
            $this->editNotes = '';
        }
    }

    public function saveCell($employeeId, $date)
    {
        $this->validate([
            'editShiftId' => 'required|exists:shifts,id',
            'editStatus' => 'required|in:scheduled,completed,cancelled,no_show',
            'editNotes' => 'nullable|string|max:500',
        ]);

        $employee = Employee::find($employeeId);

        employeeroster::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'roster_date' => $date,
            ],
            [
                'shift_id' => $this->editShiftId,
                'status' => $this->editStatus,
                'notes' => $this->editNotes,
                'department_id' => $employee->department_id,
                'shift_type' => 'regular',
            ]
        );

        session()->flash('success', 'Roster updated successfully.');
        $this->closeEditModal();
    }

    public function deleteCell($employeeId, $date)
    {
        employeeroster::where('employee_id', $employeeId)
            ->whereDate('roster_date', $date)
            ->delete();

        session()->flash('success', 'Roster entry deleted successfully.');
    }

    public function closeEditModal()
    {
        $this->reset(['editingCell', 'editShiftId', 'editStatus', 'editNotes']);
    }

    public function exportPDF()
    {
        $dates = $this->getDatesInMonth();
        $employees = $this->getEmployees();
        $rosterData = $this->getRosterData();
        $shiftsData = shifts::where('status', 'active')->orderBy('name')->get();

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $summary = [
            'total_employees' => $employees->count(),
            'total_rosters' => employeeroster::whereBetween('roster_date', [
                $startDate->copy()->startOfMonth(),
                $startDate->copy()->endOfMonth(),
            ])->when($this->department, fn ($q) => $q->where('department_id', $this->department))->count(),
            'month_name' => $startDate->format('F Y'),
        ];

        $employeeSummaries = [];
        foreach ($employees as $employee) {
            $employeeSummaries[$employee->id] = $this->getEmployeeSummary($employee->id);
        }

        $pdf = Pdf::loadView('exports.roster-pdf', [
            'employees' => $employees,
            'dates' => $dates,
            'rosterData' => $rosterData,
            'shifts' => $shiftsData,
            'summary' => $summary,
            'employeeSummaries' => $employeeSummaries,
        ])->setPaper('a4', 'landscape');

        $filename = 'roster_'.$startDate->format('Y_m').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function exportExcel()
    {
        $dates = $this->getDatesInMonth();
        $employees = $this->getEmployees();
        $rosterData = $this->getRosterData();
        $shiftsData = shifts::where('status', 'active')->orderBy('name')->get();

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $summary = [
            'total_employees' => $employees->count(),
            'total_rosters' => employeeroster::whereBetween('roster_date', [
                $startDate->copy()->startOfMonth(),
                $startDate->copy()->endOfMonth(),
            ])->when($this->department, fn ($q) => $q->where('department_id', $this->department))->count(),
            'month_name' => $startDate->format('F Y'),
        ];

        $employeeSummaries = [];
        foreach ($employees as $employee) {
            $employeeSummaries[$employee->id] = $this->getEmployeeSummary($employee->id);
        }

        $filename = 'roster_'.$startDate->format('Y_m').'.xlsx';

        return Excel::download(
            new RosterExport($employees, $dates, $rosterData, $shiftsData, $summary, $employeeSummaries),
            $filename
        );
    }

    private function getDatesInMonth()
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $daysInMonth = $startDate->daysInMonth;

        $dates = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dates[] = $startDate->copy()->day($day);
        }

        return $dates;
    }

    private function getEmployees()
    {
        return Employee::query()
            ->with(['department', 'designation'])
            ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                });
            })
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function getRosterData()
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return employeeroster::with(['shift', 'employee'])
            ->whereBetween('roster_date', [$startDate, $endDate])
            ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
            ->get()
            ->groupBy(function ($roster) {
                return $roster->employee_id.'_'.Carbon::parse($roster->roster_date)->format('Y-m-d');
            });
    }

    private function getEmployeeSummary($employeeId)
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $rosters = employeeroster::with('shift')
            ->where('employee_id', $employeeId)
            ->whereBetween('roster_date', [$startDate, $endDate])
            ->get();

        $shiftCounts = [];

        foreach ($rosters as $roster) {
            $shiftName = $roster->shift->name ?? 'Unassigned';
            $shiftCounts[$shiftName] = ($shiftCounts[$shiftName] ?? 0) + 1;
        }

        $totalDays = $rosters->count();
        $offDays = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->daysInMonth - $totalDays;

        return [
            'shifts' => $shiftCounts,
            'total_days' => $totalDays,
            'off_days' => $offDays,
        ];
    }

    public function render()
    {
        $dates = $this->getDatesInMonth();
        $employees = $this->getEmployees();
        $rosterData = $this->getRosterData();

        $departments = departments::orderBy('name')->get();
        $shifts = shifts::where('status', 'active')->orderBy('name')->get();

        // Calculate summaries for each employee
        $employeeSummaries = [];
        foreach ($employees as $employee) {
            $employeeSummaries[$employee->id] = $this->getEmployeeSummary($employee->id);
        }

        // Get overall summary
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $summary = [
            'total_employees' => $employees->count(),
            'total_rosters' => employeeroster::whereBetween('roster_date', [
                $startDate->copy()->startOfMonth(),
                $startDate->copy()->endOfMonth(),
            ])->when($this->department, fn ($q) => $q->where('department_id', $this->department))->count(),
            'month_name' => $startDate->format('F Y'),
        ];

        return view('livewire.hr.roster.viewroster', [
            'dates' => $dates,
            'employees' => $employees,
            'rosterData' => $rosterData,
            'departments' => $departments,
            'shifts' => $shifts,
            'employeeSummaries' => $employeeSummaries,
            'summary' => $summary,
        ]);
    }
}

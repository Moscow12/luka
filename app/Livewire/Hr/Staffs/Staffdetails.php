<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\employeeattendances;
use App\Models\Employeeleaves;
use App\Models\employeequalification;
use App\Models\Employeedependant;
use Carbon\Carbon;
use Livewire\Component;

class Staffdetails extends Component
{
    public $employee;
    public $activeTab = 'overview';

    // Stats
    public $totalLeaves = 0;
    public $pendingLeaves = 0;
    public $attendanceRate = 0;
    public $yearsOfService = 0;

    public function mount($id)
    {
        $this->employee = Employee::with([
            'department',
            'designation',
            'position',
            'workstation',
            'country',
            'region',
            'district',
            'ward',
            'vilstreet',
            'activeContract.department',
            'activeContract.position',
            'contracts' => function ($q) {
                $q->orderBy('start_date', 'desc')->limit(5);
            },
        ])->findOrFail($id);

        $this->loadStats();
    }

    public function loadStats()
    {
        // Years of service
        if ($this->employee->hired_date) {
            $this->yearsOfService = Carbon::parse($this->employee->hired_date)->diffInYears(now());
        }

        // Leave stats
        $currentYear = now()->year;
        $this->totalLeaves = Employeeleaves::where('employee_id', $this->employee->id)
            ->whereYear('start_date', $currentYear)
            ->where('status', 'approved')
            ->sum('days') ?? 0;

        $this->pendingLeaves = Employeeleaves::where('employee_id', $this->employee->id)
            ->whereIn('status', ['pending', 'Awaiting'])
            ->count();

        // Attendance rate (last 30 days)
        $last30Days = now()->subDays(30);
        $attendanceCount = employeeattendances::where('employee_id', $this->employee->id)
            ->whereDate('clockdate', '>=', $last30Days)
            ->distinct('clockdate')
            ->count('clockdate');

        // Assuming 22 working days in a month
        $workingDays = 22;
        $this->attendanceRate = $workingDays > 0 ? round(($attendanceCount / $workingDays) * 100, 1) : 0;
        $this->attendanceRate = min($this->attendanceRate, 100); // Cap at 100%
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        // Get additional data based on active tab
        $qualifications = [];
        $dependants = [];
        $recentLeaves = [];
        $contracts = [];

        if ($this->activeTab === 'qualifications') {
            $qualifications = employeequalification::where('employee_id', $this->employee->id)
                ->orderBy('year_obtained', 'desc')
                ->get();
        }

        if ($this->activeTab === 'dependants') {
            $dependants = Employeedependant::where('employee_id', $this->employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($this->activeTab === 'leaves') {
            $recentLeaves = Employeeleaves::where('employee_id', $this->employee->id)
                ->with('leave')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        if ($this->activeTab === 'contracts') {
            $contracts = Employeecontracts::where('employee_id', $this->employee->id)
                ->with(['department', 'position'])
                ->orderBy('start_date', 'desc')
                ->get();
        }

        return view('livewire.hr.staffs.staffdetails', [
            'qualifications' => $qualifications,
            'dependants' => $dependants,
            'recentLeaves' => $recentLeaves,
            'contracts' => $contracts,
        ]);
    }
}

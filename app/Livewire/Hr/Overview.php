<?php

namespace App\Livewire\Hr;

use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\departments;
use App\Models\Employeesalaries;
use App\Models\Employeeallowances;
use Carbon\Carbon;
use Livewire\Component;

class Overview extends Component
{
    public function getEmployeeAgeDistribution()
    {
        // SQLite-compatible age calculation
        return Employee::selectRaw("
            CASE
                WHEN (strftime('%Y', 'now') - strftime('%Y', dob)) -
                     (strftime('%m-%d', 'now') < strftime('%m-%d', dob)) < 25 THEN 'Under 25'
                WHEN (strftime('%Y', 'now') - strftime('%Y', dob)) -
                     (strftime('%m-%d', 'now') < strftime('%m-%d', dob)) BETWEEN 25 AND 34 THEN '25-34'
                WHEN (strftime('%Y', 'now') - strftime('%Y', dob)) -
                     (strftime('%m-%d', 'now') < strftime('%m-%d', dob)) BETWEEN 35 AND 44 THEN '35-44'
                WHEN (strftime('%Y', 'now') - strftime('%Y', dob)) -
                     (strftime('%m-%d', 'now') < strftime('%m-%d', dob)) BETWEEN 45 AND 54 THEN '45-54'
                ELSE '55+'
            END as age_group,
            COUNT(*) as count
        ")
        ->whereNotNull('dob')
        ->groupBy('age_group')
        ->get()
        ->sortBy(function($item) {
            $order = ['Under 25' => 1, '25-34' => 2, '35-44' => 3, '45-54' => 4, '55+' => 5];
            return $order[$item->age_group] ?? 999;
        })
        ->values();
    }

    public function getEducationLevelDistribution()
    {
        return Employee::selectRaw("
            education_level,
            COUNT(*) as count
        ")
        ->whereNotNull('education_level')
        ->groupBy('education_level')
        ->orderBy('count', 'desc')
        ->get();
    }

    public function getContractsNearExpiry()
    {
        $threeMonthsFromNow = Carbon::now()->addMonths(3);

        return Employeecontracts::with(['employee.user', 'contracttype'])
            ->where('end_date', '<=', $threeMonthsFromNow)
            ->where('end_date', '>=', Carbon::now())
            ->where('status', 'active')
            ->orderBy('end_date', 'asc')
            ->limit(10)
            ->get();
    }

    public function getDepartmentEmployeeCount()
    {
        return departments::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->get();
    }

    public function getHiringRate()
    {
        // Get employees hired in last 12 months, grouped by month (SQLite-compatible)
        return Employee::selectRaw("
            strftime('%Y-%m', hired_date) as month,
            COUNT(*) as count
        ")
        ->where('hired_date', '>=', Carbon::now()->subMonths(12))
        ->whereNotNull('hired_date')
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();
    }

    public function getMonthlySalaryAndAllowances()
    {
        $currentMonth = Carbon::now()->format('Y-m');

        // Get total salaries for current month
        $totalSalaries = Employeesalaries::whereHas('employee', function($query) {
                $query->where('status', 'Active');
            })
            ->sum('basic_salary');

        // Get total allowances for current month
        $totalAllowances = Employeeallowances::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        return [
            'total_salaries' => $totalSalaries,
            'total_allowances' => $totalAllowances,
            'total_payroll' => $totalSalaries + $totalAllowances,
            'month' => $currentMonth
        ];
    }

    public function render()
    {
        // Get all statistics
        $ageDistribution = $this->getEmployeeAgeDistribution();
        $educationDistribution = $this->getEducationLevelDistribution();
        $expiringContracts = $this->getContractsNearExpiry();
        $departmentCounts = $this->getDepartmentEmployeeCount();
        $hiringRate = $this->getHiringRate();
        $payrollStats = $this->getMonthlySalaryAndAllowances();

        // Get overall statistics
        $totalEmployees = Employee::where('status', 'Active')->count();
        $totalDepartments = departments::count();
        $contractsExpiring = $expiringContracts->count();

        // Calculate hiring rate trend
        $lastMonthHires = Employee::whereMonth('hired_date', Carbon::now()->subMonth()->month)
            ->whereYear('hired_date', Carbon::now()->subMonth()->year)
            ->count();
        $thisMonthHires = Employee::whereMonth('hired_date', Carbon::now()->month)
            ->whereYear('hired_date', Carbon::now()->year)
            ->count();
        $hiringTrend = $lastMonthHires > 0 ? (($thisMonthHires - $lastMonthHires) / $lastMonthHires) * 100 : 0;

        return view('livewire.hr.overview', [
            'totalEmployees' => $totalEmployees,
            'totalDepartments' => $totalDepartments,
            'contractsExpiring' => $contractsExpiring,
            'hiringTrend' => $hiringTrend,
            'ageDistribution' => $ageDistribution,
            'educationDistribution' => $educationDistribution,
            'expiringContracts' => $expiringContracts,
            'departmentCounts' => $departmentCounts,
            'hiringRate' => $hiringRate,
            'payrollStats' => $payrollStats,
        ]);
    }
}

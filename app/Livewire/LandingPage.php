<?php

namespace App\Livewire;

use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\Employeecontracts;
use App\Models\Employeeleaves;
use App\Models\EmployeePlanItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LandingPage extends Component
{
    public int $expiringContractsDays = 30;

    // Summary stats
    public int $totalEmployees = 0;

    public int $activeEmployees = 0;

    public int $totalDepartments = 0;

    public int $onLeaveToday = 0;

    // Chart data
    public array $attendanceChartData = [];

    public array $departmentPerformanceData = [];

    public array $employeePerformanceData = [];

    public array $leaveDistributionData = [];

    // Lists
    public $expiringContracts = [];

    public $topPerformers = [];

    public $recentLeaves = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $this->loadSummaryStats();
        $this->loadAttendanceData();
        $this->loadDepartmentPerformance();
        $this->loadEmployeePerformance();
        $this->loadLeaveData();
        $this->loadExpiringContracts();
    }

    protected function loadSummaryStats(): void
    {
        try {
            $this->totalEmployees = Employee::count();
            $this->activeEmployees = Employee::whereRaw('LOWER(status) = ?', ['active'])->count();
            $this->totalDepartments = departments::count();

            // Employees on leave today
            $today = Carbon::today();
            $this->onLeaveToday = Employeeleaves::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();
        } catch (\Exception $e) {
            // Keep default values of 0
        }
    }

    protected function loadAttendanceData(): void
    {
        // Get attendance data for the last 7 days
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i));
        }

        $labels = [];
        $presentData = [];
        $rateData = [];

        try {
            // Check if the table has data and get attendance counts
            $attendanceByDate = employeeattendances::selectRaw('DATE(clockdate) as date, COUNT(DISTINCT id) as present_count')
                ->whereDate('clockdate', '>=', $dates->first()->format('Y-m-d'))
                ->whereDate('clockdate', '<=', $dates->last()->format('Y-m-d'))
                ->groupByRaw('DATE(clockdate)')
                ->pluck('present_count', 'date')
                ->toArray();

            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('D');
                $present = $attendanceByDate[$dateStr] ?? 0;
                $presentData[] = $present;
                $rateData[] = $this->activeEmployees > 0
                    ? round(($present / $this->activeEmployees) * 100, 1)
                    : 0;
            }
        } catch (\Exception $e) {
            // If query fails, return empty data
            foreach ($dates as $date) {
                $labels[] = $date->format('D');
                $presentData[] = 0;
                $rateData[] = 0;
            }
        }

        $this->attendanceChartData = [
            'labels' => $labels,
            'present' => $presentData,
            'rate' => $rateData,
            'average_rate' => count($rateData) > 0 ? round(array_sum($rateData) / count($rateData), 1) : 0,
        ];
    }

    protected function loadDepartmentPerformance(): void
    {
        $labels = [];
        $performanceScores = [];
        $employeeCounts = [];

        try {
            $departments = departments::withCount([
                'employees' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['active']);
                },
            ])->get();

            foreach ($departments as $department) {
                // Get average performance score for employees in this department
                $avgScore = EmployeePlanItem::whereHas('employeePlan.employee', function ($query) use ($department) {
                    $query->where('department_id', $department->id);
                })
                    ->whereNotNull('score')
                    ->avg('score') ?? 0;

                $labels[] = substr($department->name, 0, 15);
                $performanceScores[] = round($avgScore, 1);
                $employeeCounts[] = $department->employees_count;
            }
        } catch (\Exception $e) {
            // If query fails, return empty data
        }

        $this->departmentPerformanceData = [
            'labels' => $labels,
            'scores' => $performanceScores,
            'employees' => $employeeCounts,
        ];
    }

    protected function loadEmployeePerformance(): void
    {
        $labels = [];
        $scores = [];
        $this->topPerformers = [];

        try {
            // Get employees with their average performance scores
            $employeesWithScores = Employee::with('department')
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->get()
                ->map(function ($employee) {
                    $avgScore = EmployeePlanItem::whereHas('employeePlan', function ($q) use ($employee) {
                        $q->where('employee_id', $employee->id);
                    })
                        ->whereNotNull('score')
                        ->avg('score');

                    $employee->avg_score = $avgScore ?? 0;

                    return $employee;
                })
                ->filter(fn ($employee) => $employee->avg_score > 0)
                ->sortByDesc('avg_score')
                ->take(10)
                ->values();

            foreach ($employeesWithScores as $employee) {
                $labels[] = $employee->first_name;
                $scores[] = round($employee->avg_score, 1);

                $this->topPerformers[] = [
                    'id' => $employee->id,
                    'name' => $employee->first_name.' '.$employee->last_name,
                    'department' => $employee->department->name ?? 'N/A',
                    'score' => round($employee->avg_score, 1),
                    'photo' => $employee->photo,
                ];
            }
        } catch (\Exception $e) {
            // If query fails, return empty data
        }

        // Performance distribution for chart
        $performanceRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        try {
            $allScores = EmployeePlanItem::whereNotNull('score')->pluck('score');

            foreach ($allScores as $score) {
                if ($score <= 20) {
                    $performanceRanges['0-20']++;
                } elseif ($score <= 40) {
                    $performanceRanges['21-40']++;
                } elseif ($score <= 60) {
                    $performanceRanges['41-60']++;
                } elseif ($score <= 80) {
                    $performanceRanges['61-80']++;
                } else {
                    $performanceRanges['81-100']++;
                }
            }
        } catch (\Exception $e) {
            // Keep default empty ranges
        }

        $this->employeePerformanceData = [
            'topLabels' => $labels,
            'topScores' => $scores,
            'distribution' => [
                'labels' => array_keys($performanceRanges),
                'values' => array_values($performanceRanges),
            ],
        ];
    }

    protected function loadLeaveData(): void
    {
        $currentYear = Carbon::now()->year;
        $leaveLabels = [];
        $leaveCounts = [];
        $leaveColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0'];
        $monthLabels = [];
        $monthCounts = [];
        $totalLeaveDays = 0;
        $leaveCoverageRate = 0;

        try {
            // Leave distribution by type
            $leavesByType = Employeeleaves::select('leave_id', DB::raw('COUNT(*) as count'))
                ->whereDate('start_date', '>=', $currentYear.'-01-01')
                ->whereDate('start_date', '<=', $currentYear.'-12-31')
                ->where('status', 'approved')
                ->groupBy('leave_id')
                ->with('leave')
                ->get();

            foreach ($leavesByType as $leave) {
                $leaveLabels[] = $leave->leave->name ?? 'Unknown';
                $leaveCounts[] = $leave->count;
            }

            // Monthly leave trend - SQLite compatible using strftime
            $approvedLeaves = Employeeleaves::whereDate('start_date', '>=', $currentYear.'-01-01')
                ->whereDate('start_date', '<=', $currentYear.'-12-31')
                ->where('status', 'approved')
                ->get();

            $monthlyLeaves = [];
            foreach ($approvedLeaves as $leave) {
                $month = Carbon::parse($leave->start_date)->month;
                $monthlyLeaves[$month] = ($monthlyLeaves[$month] ?? 0) + 1;
            }

            for ($m = 1; $m <= 12; $m++) {
                $monthLabels[] = Carbon::create()->month($m)->format('M');
                $monthCounts[] = $monthlyLeaves[$m] ?? 0;
            }

            // Calculate leave coverage rate
            $totalLeaveDays = Employeeleaves::whereDate('start_date', '>=', $currentYear.'-01-01')
                ->whereDate('start_date', '<=', $currentYear.'-12-31')
                ->where('status', 'approved')
                ->sum('days') ?? 0;

            $totalPossibleLeaveDays = $this->activeEmployees * 30;
            $leaveCoverageRate = $totalPossibleLeaveDays > 0
                ? round(($totalLeaveDays / $totalPossibleLeaveDays) * 100, 1)
                : 0;
        } catch (\Exception $e) {
            // If query fails, use default values
            for ($m = 1; $m <= 12; $m++) {
                $monthLabels[] = Carbon::create()->month($m)->format('M');
                $monthCounts[] = 0;
            }
        }

        $this->leaveDistributionData = [
            'typeLabels' => $leaveLabels,
            'typeCounts' => $leaveCounts,
            'colors' => array_slice($leaveColors, 0, max(count($leaveLabels), 1)),
            'monthLabels' => $monthLabels,
            'monthCounts' => $monthCounts,
            'coverageRate' => $leaveCoverageRate,
            'totalLeaveDays' => $totalLeaveDays,
        ];

        // Recent leave requests
        try {
            $this->recentLeaves = Employeeleaves::with(['employee', 'leave'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn ($leave) => [
                    'id' => $leave->id,
                    'employee' => ($leave->employee->first_name ?? '').' '.($leave->employee->last_name ?? ''),
                    'type' => $leave->leave->name ?? 'N/A',
                    'start' => Carbon::parse($leave->start_date)->format('M d'),
                    'end' => Carbon::parse($leave->end_date)->format('M d'),
                    'days' => $leave->days,
                    'status' => $leave->status,
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->recentLeaves = [];
        }
    }

    protected function loadExpiringContracts(): void
    {
        try {
            $expiryDate = Carbon::today()->addDays($this->expiringContractsDays);

            $this->expiringContracts = Employeecontracts::with(['employee', 'department', 'position'])
                ->where('status', 'active')
                ->whereDate('expire_date', '<=', $expiryDate)
                ->whereDate('expire_date', '>=', Carbon::today())
                ->orderBy('expire_date')
                ->limit(10)
                ->get()
                ->map(fn ($contract) => [
                    'id' => $contract->id,
                    'employee_id' => $contract->employee_id,
                    'employee_name' => ($contract->employee->first_name ?? '').' '.($contract->employee->last_name ?? ''),
                    'department' => $contract->department->name ?? 'N/A',
                    'position' => $contract->position->name ?? 'N/A',
                    'expire_date' => $contract->expire_date->format('M d, Y'),
                    'days_remaining' => Carbon::today()->diffInDays($contract->expire_date),
                    'contract_type' => $contract->contract_type,
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->expiringContracts = [];
        }
    }

    public function render()
    {
        return view('livewire.landing-page');
    }
}

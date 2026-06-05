<?php

namespace App\Livewire;

use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\Employeecontracts;
use App\Models\Employeeleaves;
use App\Models\EmployeePlanItem;
use App\Models\employeeroster;
use App\Models\shifts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LandingPage extends Component
{
    public int $expiringContractsDays = 30;

    // Current user / department context
    public ?string $employeeId = null;

    public ?string $departmentId = null;

    public bool $hasEmployee = false;

    public string $employeeName = '';

    public string $departmentName = '';

    // Personalized summary cards
    public int $deptHeadcount = 0;          // active employees in my department

    public int $activeEmployees = 0;        // = deptHeadcount (attendance rate denominator)

    public float $myLeaveDaysUsed = 0;      // my approved leave days this year

    public int $deptOnLeaveToday = 0;       // my dept on approved leave today

    public ?int $myContractDaysRemaining = null; // days left on my active contract

    // My personal attendance (this month, derived from raw fingerprint punches)
    public bool $hasFingerprint = false;

    public array $myAttendance = [
        'present_days' => 0,
        'late_days' => 0,
        'incomplete_days' => 0,
        'attendance_rate' => 0,     // (actual hours / scheduled hours) * 100
        'total_hours' => 0,
        'avg_hours' => 0,
        'scheduled_hours' => 0,
        'last_clock_in' => null,
        'last_clock_out' => null,
        'last_date' => null,
        'shift_start' => null,
        'shift_name' => null,
    ];

    // Chart data (scoped to my department)
    public array $attendanceChartData = [];

    public array $departmentPerformanceData = [];

    public array $employeePerformanceData = [];

    public array $leaveDistributionData = [];

    // Lists (scoped to my department)
    public $expiringContracts = [];

    public $topPerformers = [];

    public function mount(): void
    {
        $employee = Employee::with('department')
            ->where('user_id', Auth::id())
            ->first();

        if (! $employee) {
            // No linked employee profile (e.g. admin) — the view renders a friendly empty state.
            $this->hasEmployee = false;

            return;
        }

        $this->hasEmployee = true;
        $this->employeeId = $employee->id;
        $this->departmentId = $employee->department_id;
        $this->employeeName = trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
        $this->departmentName = $employee->department->name ?? 'N/A';

        $this->loadDashboardData($employee);
    }

    public function loadDashboardData(?Employee $employee = null): void
    {
        $this->loadSummaryStats();
        $this->loadMyAttendance($employee);
        $this->loadAttendanceData();
        $this->loadDepartmentPerformance();
        $this->loadEmployeePerformance();
        $this->loadLeaveData();
        $this->loadExpiringContracts();
    }

    /**
     * Build the logged-in user's personal attendance summary for the current
     * month from raw fingerprint punches, with shift-based late detection.
     *
     * Mirrors App\Livewire\Hr\Staffs\Attendance: punches are grouped per day,
     * the earliest is the clock in and the latest the clock out; a day is Late
     * when the clock in is after the shift start + grace.
     */
    protected function loadMyAttendance(?Employee $employee = null): void
    {
        try {
            $employee ??= Employee::find($this->employeeId);

            $fpid = $employee?->fpid;

            if (! $fpid) {
                $this->hasFingerprint = false;

                return;
            }

            $this->hasFingerprint = true;

            // Resolve shift (start time + grace minutes) for late detection.
            [$shiftStart, $graceMinutes, $shiftName] = $this->resolveShift($employee);
            $this->myAttendance['shift_start'] = $shiftStart;
            $this->myAttendance['shift_name'] = $shiftName;

            $monthStart = Carbon::now()->startOfMonth();
            $today = Carbon::today();

            $rows = employeeattendances::where('fpuser_id', $fpid)
                ->whereDate('clockdate', '>=', $monthStart->format('Y-m-d'))
                ->whereDate('clockdate', '<=', $today->format('Y-m-d'))
                ->get();

            $days = $rows
                ->groupBy(fn ($row) => Carbon::parse($row->clockdate)->format('Y-m-d'))
                ->map(function ($dayRows, $date) use ($shiftStart, $graceMinutes) {
                    $byTime = $dayRows->sortBy(fn ($r) => $r->clocktime ?? $r->clock_in ?? '');
                    $first = $byTime->first();
                    $last = $byTime->last();

                    $clockIn = $first->clock_in ?? $first->clocktime;
                    $clockOut = $dayRows->count() > 1
                        ? ($last->clock_out ?? $last->clocktime)
                        : ($first->clock_out ?? null);

                    // Single afternoon punch = checkout only.
                    if ($dayRows->count() === 1 && empty($first->clock_in) && empty($first->clock_out) && $first->clocktime) {
                        if ((int) Carbon::parse($first->clocktime)->format('H') >= 12) {
                            $clockOut = $first->clocktime;
                            $clockIn = null;
                        }
                    }

                    $hours = ($clockIn && $clockOut)
                        ? abs(Carbon::parse($clockIn)->floatDiffInHours(Carbon::parse($clockOut)))
                        : 0.0;

                    $status = $dayRows->pluck('clock_status')->filter()->first();
                    if (! $status) {
                        if ($clockIn && $clockOut) {
                            $status = $this->isLate($clockIn, $shiftStart, $graceMinutes) ? 'Late' : 'Present';
                        } else {
                            $status = 'Incomplete';
                        }
                    }

                    return [
                        'date' => $date,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status,
                        'hours' => round($hours, 2),
                    ];
                })
                ->sortBy('date')
                ->values();

            $present = $days->whereIn('status', ['Present', 'Late'])->count();
            $late = $days->where('status', 'Late')->count();
            $incomplete = $days->where('status', 'Incomplete')->count();

            $workedDays = $days->where('hours', '>', 0);
            $totalHours = $workedDays->sum('hours');
            $avgHours = $workedDays->count() > 0 ? $totalHours / $workedDays->count() : 0;

            // Attendance Rate = (Actual Hours Worked / Scheduled Hours) * 100,
            // where scheduled hours = 8h per working day (Mon–Fri) elapsed this month.
            $hoursPerDay = 8;
            $scheduledHours = $this->workingDaysBetween($monthStart, $today) * $hoursPerDay;
            $rate = $scheduledHours > 0 ? round(($totalHours / $scheduledHours) * 100, 1) : 0;

            $latest = $days->last();

            $this->myAttendance = array_merge($this->myAttendance, [
                'present_days' => $present,
                'late_days' => $late,
                'incomplete_days' => $incomplete,
                'attendance_rate' => min($rate, 100),
                'total_hours' => round($totalHours, 1),
                'avg_hours' => round($avgHours, 1),
                'scheduled_hours' => $scheduledHours,
                'last_clock_in' => $latest['clock_in'] ?? null,
                'last_clock_out' => $latest['clock_out'] ?? null,
                'last_date' => $latest['date'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Keep defaults on any failure.
        }
    }

    /**
     * Resolve [shiftStart, graceMinutes, shiftName] for an employee from the
     * most recent shift rostered for them or their department; otherwise the
     * shift flagged as default; otherwise 08:00 with a 15-minute grace.
     */
    protected function resolveShift(Employee $employee): array
    {
        $shift = employeeroster::query()
            ->with('shift')
            ->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
                if ($employee->department_id) {
                    $q->orWhere('department_id', $employee->department_id);
                }
            })
            ->whereHas('shift')
            ->latest('roster_date')
            ->latest()
            ->first()?->shift;

        // No roster -> fall back to the configured default shift.
        $shift ??= shifts::default();

        if ($shift && $shift->start_time) {
            return [
                Carbon::parse($shift->start_time)->format('H:i:s'),
                (int) ($shift->count_late ?? 0),
                $shift->name,
            ];
        }

        return ['08:00:00', 15, null];
    }

    /**
     * A clock-in is late when it falls after shift start + grace.
     */
    protected function isLate(?string $clockIn, ?string $shiftStart, int $graceMinutes): bool
    {
        if (! $clockIn || ! $shiftStart) {
            return false;
        }

        $in = Carbon::parse($clockIn)->format('H:i:s');
        $threshold = Carbon::parse($shiftStart)->addMinutes($graceMinutes)->format('H:i:s');

        return $in > $threshold;
    }

    /**
     * Count weekdays (Mon–Fri) between two dates, inclusive.
     */
    protected function workingDaysBetween(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    protected function loadSummaryStats(): void
    {
        try {
            $today = Carbon::today();

            // Active headcount in my department (also used as the attendance-rate denominator).
            $this->deptHeadcount = Employee::where('department_id', $this->departmentId)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->count();
            $this->activeEmployees = $this->deptHeadcount;

            // My own approved leave days taken this year.
            $this->myLeaveDaysUsed = (float) (Employeeleaves::where('employee_id', $this->employeeId)
                ->where('status', 'approved')
                ->whereYear('start_date', $today->year)
                ->sum('days') ?? 0);

            // My department's employees on approved leave today.
            $this->deptOnLeaveToday = Employeeleaves::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId))
                ->count();

            // Days remaining on my nearest active contract.
            $contract = Employeecontracts::where('employee_id', $this->employeeId)
                ->where('status', 'active')
                ->whereDate('expire_date', '>=', $today)
                ->orderBy('expire_date')
                ->first();
            $this->myContractDaysRemaining = $contract
                ? (int) $today->diffInDays($contract->expire_date)
                : null;
        } catch (\Exception $e) {
            // Keep default values
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
            // Attendance is linked to employees via fingerprint device id:
            // Employee.fpid -> fpusers.fpdevice_id -> employeeattendances.fpuser_id.
            $deptDeviceIds = Employee::where('department_id', $this->departmentId)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->whereNotNull('fpid')
                ->pluck('fpid')
                ->all();

            // Get attendance counts for my department's employees only.
            $attendanceByDate = employeeattendances::selectRaw('DATE(clockdate) as date, COUNT(DISTINCT fpuser_id) as present_count')
                ->whereIn('fpuser_id', $deptDeviceIds)
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
            $departments = departments::where('id', $this->departmentId)
                ->withCount([
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
            // Get my department's employees with their average performance scores
            $employeesWithScores = Employee::with('department')
                ->where('department_id', $this->departmentId)
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
            $allScores = EmployeePlanItem::whereHas('employeePlan.employee', fn ($q) => $q->where('department_id', $this->departmentId))
                ->whereNotNull('score')
                ->pluck('score');

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
        // Leave distribution by type + coverage, scoped to my department.
        // (Monthly trend and recent leaves now live on the HR Overview page.)
        $currentYear = Carbon::now()->year;
        $leaveLabels = [];
        $leaveCounts = [];
        $leaveColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0'];
        $totalLeaveDays = 0;
        $leaveCoverageRate = 0;

        try {
            // Leave distribution by type (my department)
            $leavesByType = Employeeleaves::select('leave_id', DB::raw('COUNT(*) as count'))
                ->whereDate('start_date', '>=', $currentYear.'-01-01')
                ->whereDate('start_date', '<=', $currentYear.'-12-31')
                ->where('status', 'approved')
                ->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId))
                ->groupBy('leave_id')
                ->with('leave')
                ->get();

            foreach ($leavesByType as $leave) {
                $leaveLabels[] = $leave->leave->name ?? 'Unknown';
                $leaveCounts[] = $leave->count;
            }

            // Coverage rate & total days (my department)
            $totalLeaveDays = Employeeleaves::whereDate('start_date', '>=', $currentYear.'-01-01')
                ->whereDate('start_date', '<=', $currentYear.'-12-31')
                ->where('status', 'approved')
                ->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId))
                ->sum('days') ?? 0;

            $totalPossibleLeaveDays = $this->deptHeadcount * 30;
            $leaveCoverageRate = $totalPossibleLeaveDays > 0
                ? round(($totalLeaveDays / $totalPossibleLeaveDays) * 100, 1)
                : 0;
        } catch (\Exception $e) {
            // If query fails, fall back to empty distribution
        }

        $this->leaveDistributionData = [
            'typeLabels' => $leaveLabels,
            'typeCounts' => $leaveCounts,
            'colors' => array_slice($leaveColors, 0, max(count($leaveLabels), 1)),
            'coverageRate' => $leaveCoverageRate,
            'totalLeaveDays' => $totalLeaveDays,
        ];
    }

    protected function loadExpiringContracts(): void
    {
        try {
            $expiryDate = Carbon::today()->addDays($this->expiringContractsDays);

            $this->expiringContracts = Employeecontracts::with(['employee', 'department', 'position'])
                ->where('status', 'active')
                ->whereDate('expire_date', '<=', $expiryDate)
                ->whereDate('expire_date', '>=', Carbon::today())
                ->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId))
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

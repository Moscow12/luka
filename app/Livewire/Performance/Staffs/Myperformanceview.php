<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanImplementation;
use App\Models\employeeattendances;
use App\Models\fpusers;
use App\Models\PerformanceEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Myperformanceview extends Component
{
    public $employee;
    public $hasEmployeeRecord = false;

    // Period filter
    public $periodFilter = 'current_month';

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())
            ->with(['department', 'position', 'designation'])
            ->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
        }
    }

    public function getPlanningStats()
    {
        if (!$this->employee) {
            return [
                'total_plans' => 0,
                'active_plans' => 0,
                'completed_plans' => 0,
                'total_goals' => 0,
                'overall_progress' => 0,
            ];
        }

        $plans = EmployeePlan::where('employee_id', $this->employee->id)
            ->with('employeePlanItems')
            ->get();

        $totalGoals = 0;
        $totalProgress = 0;
        $goalCount = 0;

        foreach ($plans as $plan) {
            foreach ($plan->employeePlanItems as $item) {
                $totalGoals++;
                if ($item->target_value && $item->target_value > 0) {
                    $progress = min(($item->total_achieved / $item->target_value) * 100, 100);
                    $totalProgress += $progress;
                    $goalCount++;
                }
            }
        }

        return [
            'total_plans' => $plans->count(),
            'active_plans' => $plans->where('status', 'active')->count(),
            'completed_plans' => $plans->where('status', 'completed')->count(),
            'total_goals' => $totalGoals,
            'overall_progress' => $goalCount > 0 ? round($totalProgress / $goalCount, 1) : 0,
        ];
    }

    public function getImplementationStats()
    {
        if (!$this->employee) {
            return [
                'total_implementations' => 0,
                'this_month' => 0,
                'pending_verification' => 0,
                'verified' => 0,
                'days_since_first' => 0,
                'recent_implementations' => collect(),
            ];
        }

        $implementations = EmployeePlanImplementation::whereHas('employeePlanItem.employeePlan', function ($q) {
            $q->where('employee_id', $this->employee->id);
        })->with(['employeePlanItem'])->get();

        $firstImplementation = $implementations->sortBy('implementation_date')->first();
        $daysSinceFirst = $firstImplementation
            ? (int) floor(abs(Carbon::parse($firstImplementation->implementation_date)->diffInDays(now())))
            : 0;

        $thisMonth = $implementations->filter(function ($impl) {
            return Carbon::parse($impl->implementation_date)->isCurrentMonth();
        })->count();

        $recentImplementations = EmployeePlanImplementation::whereHas('employeePlanItem.employeePlan', function ($q) {
            $q->where('employee_id', $this->employee->id);
        })
            ->with(['employeePlanItem'])
            ->orderBy('implementation_date', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_implementations' => $implementations->count(),
            'this_month' => $thisMonth,
            'pending_verification' => $implementations->where('status', 'pending')->count(),
            'verified' => $implementations->where('status', 'verified')->count(),
            'days_since_first' => $daysSinceFirst,
            'recent_implementations' => $recentImplementations,
        ];
    }

    public function getActivePlansWithProgress()
    {
        if (!$this->employee) {
            return collect();
        }

        return EmployeePlan::where('employee_id', $this->employee->id)
            ->whereIn('status', ['active', 'completed'])
            ->with(['employeePlanItems.implementations'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($plan) {
                $items = $plan->employeePlanItems;
                $totalProgress = 0;
                $itemCount = 0;

                foreach ($items as $item) {
                    if ($item->target_value && $item->target_value > 0) {
                        $achieved = $item->total_achieved ?? 0;
                        $progress = min(($achieved / $item->target_value) * 100, 100);
                        $totalProgress += $progress;
                        $itemCount++;
                    }
                }

                $plan->calculated_progress = $itemCount > 0 ? round($totalProgress / $itemCount, 1) : 0;
                $plan->goal_count = $items->count();

                // Calculate days since plan started
                $startedAt = $plan->assigned_at ?: $plan->created_at;
                $plan->days_active = (int) floor(abs(Carbon::parse($startedAt)->diffInDays(now())));

                return $plan;
            });
    }

    public function getAssignedDutiesStats()
    {
        if (!$this->employee) {
            return [
                'total' => 0,
                'assigned' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'overdue' => 0,
                'completion_rate' => 0,
                'recent_duties' => collect(),
            ];
        }

        $duties = EmployeeAssignedDuty::where('employee_id', $this->employee->id)
            ->where('is_active', true)
            ->get();

        $total = $duties->count();
        $completed = $duties->where('status', 'completed')->count();
        $overdue = $duties->filter(function ($duty) {
            return $duty->end_date && $duty->end_date->isPast() && $duty->status !== 'completed';
        })->count();

        $recentDuties = EmployeeAssignedDuty::where('employee_id', $this->employee->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('end_date', 'asc')
            ->limit(5)
            ->get();

        return [
            'total' => $total,
            'assigned' => $duties->where('status', 'assigned')->count(),
            'in_progress' => $duties->where('status', 'in_progress')->count(),
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'recent_duties' => $recentDuties,
        ];
    }

    public function getAttendanceStats()
    {
        if (!$this->employee || !$this->employee->fpid) {
            return [
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'late_days' => 0,
                'attendance_rate' => 0,
                'this_month_rate' => 0,
                'recent_attendance' => collect(),
            ];
        }

        // Get FP user linked to this employee
        $fpUser = fpusers::where('fpdevice_id', $this->employee->fpid)->first();

        if (!$fpUser) {
            return [
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'late_days' => 0,
                'attendance_rate' => 0,
                'this_month_rate' => 0,
                'recent_attendance' => collect(),
            ];
        }

        // Get attendance records for this month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $today = Carbon::now();

        // Calculate working days this month (excluding weekends)
        $workingDays = 0;
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $today && $currentDate <= $endOfMonth) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        // Get attendance records
        $attendanceRecords = employeeattendances::where('fpuser_id', $fpUser->fpdevice_id)
            ->whereBetween('clockdate', [$startOfMonth->format('Y-m-d'), $today->format('Y-m-d')])
            ->get();

        // Count unique present days
        $presentDays = $attendanceRecords->pluck('clockdate')->unique()->count();

        // Get recent attendance (last 7 days)
        $recentAttendance = employeeattendances::where('fpuser_id', $fpUser->fpdevice_id)
            ->orderBy('clockdate', 'desc')
            ->orderBy('clocktime', 'desc')
            ->limit(10)
            ->get();

        // Calculate late days (assuming 8:00 AM is the expected clock-in time)
        $lateDays = $attendanceRecords->filter(function ($record) {
            if ($record->clock_in) {
                $clockIn = Carbon::parse($record->clock_in);
                return $clockIn->format('H:i') > '08:30';
            }
            return false;
        })->pluck('clockdate')->unique()->count();

        $attendanceRate = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;

        return [
            'total_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days' => max(0, $workingDays - $presentDays),
            'late_days' => $lateDays,
            'attendance_rate' => $attendanceRate,
            'this_month_rate' => $attendanceRate,
            'recent_attendance' => $recentAttendance,
        ];
    }

    public function getEvaluationStats()
    {
        if (!$this->employee) {
            return [
                'total_evaluations' => 0,
                'approved' => 0,
                'pending' => 0,
                'average_score' => 0,
                'latest_evaluation' => null,
            ];
        }

        $evaluations = PerformanceEvaluation::where('employee_id', $this->employee->id)
            ->with(['employeePlan'])
            ->get();

        $approvedEvaluations = $evaluations->where('status', 'approved');
        $averageScore = $approvedEvaluations->count() > 0
            ? round($approvedEvaluations->avg('final_score'), 1)
            : 0;

        $latestEvaluation = PerformanceEvaluation::where('employee_id', $this->employee->id)
            ->with(['employeePlan'])
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'total_evaluations' => $evaluations->count(),
            'approved' => $approvedEvaluations->count(),
            'pending' => $evaluations->whereIn('status', ['submitted', 'under_review'])->count(),
            'average_score' => $averageScore,
            'latest_evaluation' => $latestEvaluation,
        ];
    }

    public function getPerformanceRanking()
    {
        if (!$this->employee) {
            return [
                'rank' => 0,
                'total_employees' => 0,
                'percentile' => 0,
            ];
        }

        // Get all employees in the same department with their performance scores
        $departmentId = $this->employee->department_id;

        $employeesWithScores = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->get()
            ->map(function ($emp) {
                $plans = EmployeePlan::where('employee_id', $emp->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->with('employeePlanItems')
                    ->get();

                $totalProgress = 0;
                $itemCount = 0;

                foreach ($plans as $plan) {
                    foreach ($plan->employeePlanItems as $item) {
                        if ($item->target_value && $item->target_value > 0) {
                            $achieved = $item->total_achieved ?? 0;
                            $progress = min(($achieved / $item->target_value) * 100, 100);
                            $totalProgress += $progress;
                            $itemCount++;
                        }
                    }
                }

                $emp->performance_score = $itemCount > 0 ? round($totalProgress / $itemCount, 1) : 0;
                return $emp;
            })
            ->sortByDesc('performance_score')
            ->values();

        $totalEmployees = $employeesWithScores->count();
        $rank = $employeesWithScores->search(function ($emp) {
            return $emp->id === $this->employee->id;
        });

        $rank = $rank !== false ? $rank + 1 : $totalEmployees;
        $percentile = $totalEmployees > 0 ? round((($totalEmployees - $rank + 1) / $totalEmployees) * 100, 1) : 0;

        return [
            'rank' => $rank,
            'total_employees' => $totalEmployees,
            'percentile' => $percentile,
        ];
    }

    public function render()
    {
        return view('livewire.performance.staffs.myperformanceview', [
            'planningStats' => $this->getPlanningStats(),
            'implementationStats' => $this->getImplementationStats(),
            'activePlans' => $this->getActivePlansWithProgress(),
            'dutiesStats' => $this->getAssignedDutiesStats(),
            'attendanceStats' => $this->getAttendanceStats(),
            'evaluationStats' => $this->getEvaluationStats(),
            'ranking' => $this->getPerformanceRanking(),
        ]);
    }
}

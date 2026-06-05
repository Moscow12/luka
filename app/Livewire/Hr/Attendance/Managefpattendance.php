<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\Employeeleaves;
use App\Models\employeeroster;
use App\Models\fpusers;
use App\Models\shifts;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Managefpattendance extends Component
{
    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $perPage = 20;

    public $page = 1;

    // Score modal state
    public bool $showScoreModal = false;

    public array $score = [];

    public function mount()
    {
        // Default to last 7 days
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->page = 1;
    }

    public function updatingDateFrom()
    {
        $this->page = 1;
    }

    public function updatingDateTo()
    {
        $this->page = 1;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function clearFilters()
    {
        $this->reset(['search']);
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->page = 1;
    }

    /**
     * Build and show the attendance score summary for one fingerprint user
     * over the selected date range — mirrors the per-day aggregation and
     * shift-based late detection used on /hr/staffs/attendance.
     */
    public function showScore($fpDeviceId)
    {
        $fpuser = fpusers::where('fpdevice_id', $fpDeviceId)->first();

        // The fingerprint user links to an Employee via Employee.fpid.
        $employee = Employee::where('fpid', $fpDeviceId)->first();

        [$shiftStart, $graceMinutes, $shiftName, $shiftSource] = $this->resolveShift($employee);

        // Aggregate raw punches into one record per day.
        $rows = employeeattendances::where('fpuser_id', $fpDeviceId)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->get();

        $days = $rows
            ->groupBy(fn ($row) => Carbon::parse($row->clockdate)->format('Y-m-d'))
            ->map(fn ($dayRows, $date) => $this->aggregateDay($date, $dayRows, $shiftStart, $graceMinutes));

        $present = $days->where('clock_status', 'Present')->count();
        $late = $days->where('clock_status', 'Late')->count();
        $incomplete = $days->where('clock_status', 'Incomplete')->count();

        $workedDays = $days->where('hours', '>', 0);
        $totalHours = round($workedDays->sum('hours'), 1);
        $avgHours = $workedDays->count() > 0 ? round($totalHours / $workedDays->count(), 1) : 0;

        // Approved leave days that overlap the selected range.
        $leaveDays = $this->countLeaveDays($employee);

        // No-show = working days (Mon–Fri) in range with neither a punch nor leave.
        $workingDays = $this->workingDaysBetween(Carbon::parse($this->dateFrom), Carbon::parse($this->dateTo));
        $daysWithPunch = $days->count();
        $noShow = max(0, $workingDays - $daysWithPunch - $leaveDays);

        $this->score = [
            'name' => $fpuser->name ?? ($employee?->getFullName() ?? 'Unknown'),
            'fp_id' => $fpDeviceId,
            'employee_no' => $employee?->employee_no,
            'linked' => (bool) $employee,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'shift_start' => $shiftStart,
            'shift_name' => $shiftName,
            'shift_source' => $shiftSource,
            'grace' => $graceMinutes,
            'present' => $present,
            'late' => $late,
            'incomplete' => $incomplete,
            'leave' => $leaveDays,
            'no_show' => $noShow,
            'total_hours' => $totalHours,
            'avg_hours' => $avgHours,
            'worked_days' => $workedDays->count(),
            'working_days' => $workingDays,
        ];

        $this->showScoreModal = true;
    }

    public function closeScore()
    {
        $this->reset(['showScoreModal', 'score']);
    }

    /**
     * Collapse a single day's punches into one record (clock in = earliest,
     * clock out = latest) with a derived status and worked hours.
     */
    protected function aggregateDay(string $date, $dayRows, ?string $shiftStart, int $graceMinutes): array
    {
        $byTime = $dayRows->sortBy(fn ($r) => $r->clocktime ?? $r->clock_in ?? '');
        $first = $byTime->first();
        $last = $byTime->last();

        $clockIn = $first->clock_in ?? $first->clocktime;
        $clockOut = $dayRows->count() > 1 ? ($last->clock_out ?? $last->clocktime) : ($first->clock_out ?? null);

        // A single afternoon punch is treated as a checkout only.
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
            'clock_status' => $status,
            'hours' => round($hours, 2),
        ];
    }

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
     * Resolve [shiftStart, graceMinutes, shiftName, source] for an employee:
     * a shift rostered within the selected range, otherwise the default shift,
     * otherwise the hardcoded fallback.
     */
    protected function resolveShift(?Employee $employee): array
    {
        $rosterShift = null;

        if ($employee) {
            $rosterShift = employeeroster::query()
                ->with('shift')
                ->where(function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                    if ($employee->department_id) {
                        $q->orWhere('department_id', $employee->department_id);
                    }
                })
                ->whereHas('shift')
                ->whereBetween('roster_date', [$this->dateFrom, $this->dateTo])
                ->latest('roster_date')
                ->latest()
                ->first()?->shift;
        }

        $shift = $rosterShift ?? shifts::default();
        $source = $rosterShift ? 'roster' : ($shift ? 'default' : 'fallback');

        if ($shift && $shift->start_time) {
            return [
                Carbon::parse($shift->start_time)->format('H:i:s'),
                (int) ($shift->count_late ?? 0),
                $shift->name,
                $source,
            ];
        }

        return ['08:00:00', 15, null, 'fallback'];
    }

    /**
     * Approved leave days for the employee overlapping the selected range.
     */
    protected function countLeaveDays(?Employee $employee): int
    {
        if (! $employee) {
            return 0;
        }

        $count = 0;

        $leaves = Employeeleaves::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $this->dateTo)
            ->whereDate('end_date', '>=', $this->dateFrom)
            ->get(['start_date', 'end_date']);

        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date)->max($from);
            $end = Carbon::parse($leave->end_date)->min($to);

            foreach (CarbonPeriod::create($start, $end) as $day) {
                if (! $day->isWeekend()) {
                    $count++;
                }
            }
        }

        return $count;
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

    public function render()
    {
        // Get date range for columns
        $startDate = Carbon::parse($this->dateFrom);
        $endDate = Carbon::parse($this->dateTo);
        $dates = collect(CarbonPeriod::create($startDate, $endDate))->map(fn ($date) => $date->format('Y-m-d'));

        // Get all fpusers with search filter
        $usersQuery = fpusers::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name');

        $totalUsers = $usersQuery->count();
        $totalPages = ceil($totalUsers / $this->perPage);

        // Paginate users
        $users = $usersQuery
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        // Get attendance records for these users in date range
        $userIds = $users->pluck('fpdevice_id')->toArray();

        $attendances = employeeattendances::query()
            ->whereIn('fpuser_id', $userIds)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->orderBy('clocktime')
            ->get()
            ->groupBy(['fpuser_id', 'clockdate']);

        // Scheduled hours over the selected range: 8 hours per selected day.
        $hoursPerDay = 8;
        $scheduledHours = $hoursPerDay * $dates->count();

        // Build attendance matrix
        $attendanceMatrix = [];
        foreach ($users as $index => $user) {
            $row = [
                'index' => ($this->page - 1) * $this->perPage + $index + 1,
                'user' => $user,
                'dates' => [],
            ];

            $actualHours = 0.0;

            foreach ($dates as $date) {
                $dayAttendance = $attendances[$user->fpdevice_id][$date] ?? collect();

                if ($dayAttendance->isEmpty()) {
                    $row['dates'][$date] = [
                        'status' => 'no_show',
                        'clock_in' => null,
                        'clock_out' => null,
                    ];
                } else {
                    // Get first check-in (earliest time)
                    $checkIn = $dayAttendance->sortBy('clocktime')->first();

                    // Get last check-out (latest time, different from check-in)
                    $checkOut = $dayAttendance->count() > 1
                        ? $dayAttendance->sortByDesc('clocktime')->first()
                        : null;

                    // If only one record, determine if it's IN or OUT based on time
                    if ($dayAttendance->count() === 1) {
                        $hour = (int) Carbon::parse($checkIn->clocktime)->format('H');
                        if ($hour >= 12) {
                            // Afternoon - likely checkout only
                            $checkOut = $checkIn;
                            $checkIn = null;
                        }
                    }

                    $row['dates'][$date] = [
                        'status' => 'present',
                        'clock_in' => $checkIn?->clocktime,
                        'clock_out' => $checkOut?->clocktime,
                        'has_checkout' => $checkOut !== null && $checkIn !== null && $checkOut->id !== $checkIn?->id,
                    ];

                    // Accumulate worked hours only when both in and out are known.
                    if ($checkIn && $checkOut && $checkIn->id !== $checkOut->id) {
                        $actualHours += abs(
                            Carbon::parse($checkIn->clocktime)->floatDiffInHours(Carbon::parse($checkOut->clocktime))
                        );
                    }
                }
            }

            // Attendance Rate = (Actual Hours Worked / Scheduled Hours) * 100
            $row['actual_hours'] = round($actualHours, 1);
            $row['attendance_rate'] = $scheduledHours > 0
                ? round(($actualHours / $scheduledHours) * 100, 1)
                : 0;

            $attendanceMatrix[] = $row;
        }

        return view('livewire.hr.attendance.managefpattendance', [
            'attendanceMatrix' => $attendanceMatrix,
            'dates' => $dates,
            'totalUsers' => $totalUsers,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
            'scheduledHours' => $scheduledHours,
        ]);
    }
}

<?php

namespace App\Livewire\Hr\Attendance;

use App\Exports\FpAttendanceExport;
use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\Employeeleaves;
use App\Models\employeeroster;
use App\Models\fpusers;
use App\Models\shifts;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Managefpattendance extends Component
{
    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $perPage = 20;

    public $page = 1;

    public bool $showScoreModal = false;

    public array $score = [];

    public function mount()
    {
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function updatingSearch()   { $this->page = 1; }
    public function updatingDateFrom() { $this->page = 1; }
    public function updatingDateTo()   { $this->page = 1; }

    public function previousPage() { if ($this->page > 1) $this->page--; }
    public function nextPage()     { $this->page++; }

    public function clearFilters()
    {
        $this->reset(['search']);
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->page     = 1;
    }

    // -------------------------------------------------------------------------
    // Score modal
    // -------------------------------------------------------------------------

    public function showScore($fpDeviceId)
    {
        $fpuser   = fpusers::where('fpdevice_id', $fpDeviceId)->first();
        $employee = Employee::where('fpid', $fpDeviceId)->first();

        [$shiftStart, $graceMinutes, $shiftName, $shiftSource] = $this->resolveShift($employee);

        $rows = employeeattendances::where('fpuser_id', $fpDeviceId)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->get();

        $days = $rows
            ->groupBy(fn ($row) => Carbon::parse($row->clockdate)->format('Y-m-d'))
            ->map(fn ($dayRows, $date) => $this->aggregateDay($date, $dayRows, $shiftStart, $graceMinutes));

        $present    = $days->where('clock_status', 'Present')->count();
        $late       = $days->where('clock_status', 'Late')->count();
        $incomplete = $days->where('clock_status', 'Incomplete')->count();

        $workedDays = $days->where('hours', '>', 0);
        $totalHours = round($workedDays->sum('hours'), 1);
        $avgHours   = $workedDays->count() > 0 ? round($totalHours / $workedDays->count(), 1) : 0;

        $leaveDays   = $this->countLeaveDays($employee);
        $workingDays = $this->workingDaysBetween(Carbon::parse($this->dateFrom), Carbon::parse($this->dateTo));
        $noShow      = max(0, $workingDays - $days->count() - $leaveDays);

        $this->score = [
            'name'         => $fpuser->name ?? ($employee?->getFullName() ?? 'Unknown'),
            'fp_id'        => $fpDeviceId,
            'employee_no'  => $employee?->employee_no,
            'linked'       => (bool) $employee,
            'date_from'    => $this->dateFrom,
            'date_to'      => $this->dateTo,
            'shift_start'  => $shiftStart,
            'shift_name'   => $shiftName,
            'shift_source' => $shiftSource,
            'grace'        => $graceMinutes,
            'present'      => $present,
            'late'         => $late,
            'incomplete'   => $incomplete,
            'leave'        => $leaveDays,
            'no_show'      => $noShow,
            'total_hours'  => $totalHours,
            'avg_hours'    => $avgHours,
            'worked_days'  => $workedDays->count(),
            'working_days' => $workingDays,
        ];

        $this->showScoreModal = true;
    }

    public function closeScore()
    {
        $this->reset(['showScoreModal', 'score']);
    }

    // -------------------------------------------------------------------------
    // Exports
    // -------------------------------------------------------------------------

    public function exportExcel()
    {
        [$rows, $dates] = $this->buildExportData();

        $filename = 'attendance_'.$this->dateFrom.'_to_'.$this->dateTo.'.xlsx';

        return Excel::download(new FpAttendanceExport($rows, $dates, $this->dateFrom, $this->dateTo), $filename);
    }

    public function exportPdf()
    {
        [$rows, $dates] = $this->buildExportData();

        $pdf = Pdf::loadView('exports.fp-attendance-pdf', [
            'rows'     => $rows,
            'dates'    => $dates,
            'dateFrom' => $this->dateFrom,
            'dateTo'   => $this->dateTo,
        ])->setPaper('a4', 'landscape');

        $filename = 'attendance_'.$this->dateFrom.'_to_'.$this->dateTo.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Build a flat export-ready dataset: only FP users who have at least one
     * check-in within the selected period, sorted by name, one row per user.
     *
     * Each row contains a 'dates' map keyed by date string. Cells include
     * clock_in, clock_out, has_checkout, attendance_status, and status.
     *
     * @return array{0: array, 1: \Illuminate\Support\Collection}
     */
    protected function buildExportData(): array
    {
        $dates = collect(CarbonPeriod::create(
            Carbon::parse($this->dateFrom),
            Carbon::parse($this->dateTo)
        ))->map(fn ($d) => $d->format('Y-m-d'));

        // Only users with at least one punch in the range
        $fpDeviceIds = employeeattendances::whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->distinct()
            ->pluck('fpuser_id');

        $users = fpusers::whereIn('fpdevice_id', $fpDeviceIds)
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->get();

        $attendances = employeeattendances::whereIn('fpuser_id', $fpDeviceIds)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->orderBy('clocktime')
            ->get()
            ->groupBy(['fpuser_id', 'clockdate']);

        // Scheduled hours for attendance rate: 8h × number of days in range
        $scheduledHours = 8 * $dates->count();

        $rows = [];
        foreach ($users as $user) {
            $employee = Employee::where('fpid', $user->fpdevice_id)->first();
            [$shiftStart, $graceMinutes] = $this->resolveShift($employee);

            $actualHours = 0.0;
            $dateCells   = [];

            foreach ($dates as $date) {
                $dayAttendance = $attendances[$user->fpdevice_id][$date] ?? collect();

                if ($dayAttendance->isEmpty()) {
                    $dateCells[$date] = [
                        'status'            => 'no_show',
                        'clock_in'          => null,
                        'clock_out'         => null,
                        'has_checkout'      => false,
                        'attendance_status' => '',
                    ];
                    continue;
                }

                $sorted   = $dayAttendance->sortBy('clocktime');
                $checkIn  = $sorted->first();
                $checkOut = $dayAttendance->count() > 1 ? $sorted->last() : null;

                // Single afternoon punch → checkout only
                if ($dayAttendance->count() === 1) {
                    $hour = (int) Carbon::parse($checkIn->clocktime)->format('H');
                    if ($hour >= 12) {
                        $checkOut = $checkIn;
                        $checkIn  = null;
                    }
                }

                $hasCheckout = $checkOut && $checkIn && $checkOut->id !== $checkIn->id;

                if ($hasCheckout) {
                    $actualHours += abs(
                        Carbon::parse($checkIn->clocktime)->floatDiffInHours(Carbon::parse($checkOut->clocktime))
                    );
                }

                // Determine attendance status
                $attendStatus = $dayAttendance->pluck('clock_status')->filter()->first();
                if (! $attendStatus) {
                    if ($checkIn && $checkOut && $hasCheckout) {
                        $attendStatus = $this->isLate($checkIn->clocktime, $shiftStart, $graceMinutes) ? 'Late' : 'Present';
                    } else {
                        $attendStatus = 'Incomplete';
                    }
                }

                $dateCells[$date] = [
                    'status'            => 'present',
                    'clock_in'          => $checkIn?->clocktime,
                    'clock_out'         => $checkOut?->clocktime,
                    'has_checkout'      => $hasCheckout,
                    'attendance_status' => $attendStatus,
                ];
            }

            $rows[] = [
                'fp_id'           => $user->fpdevice_id,
                'name'            => $user->name,
                'dates'           => $dateCells,
                'actual_hours'    => round($actualHours, 1),
                'attendance_rate' => $scheduledHours > 0
                    ? round(($actualHours / $scheduledHours) * 100, 1)
                    : 0,
            ];
        }

        return [$rows, $dates];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function aggregateDay(string $date, $dayRows, ?string $shiftStart, int $graceMinutes): array
    {
        $byTime   = $dayRows->sortBy(fn ($r) => $r->clocktime ?? $r->clock_in ?? '');
        $first    = $byTime->first();
        $last     = $byTime->last();

        $clockIn  = $first->clock_in ?? $first->clocktime;
        $clockOut = $dayRows->count() > 1 ? ($last->clock_out ?? $last->clocktime) : ($first->clock_out ?? null);

        if ($dayRows->count() === 1 && empty($first->clock_in) && empty($first->clock_out) && $first->clocktime) {
            if ((int) Carbon::parse($first->clocktime)->format('H') >= 12) {
                $clockOut = $first->clocktime;
                $clockIn  = null;
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

        return ['date' => $date, 'clock_status' => $status, 'hours' => round($hours, 2)];
    }

    protected function isLate(?string $clockIn, ?string $shiftStart, int $graceMinutes): bool
    {
        if (! $clockIn || ! $shiftStart) return false;

        $in        = Carbon::parse($clockIn)->format('H:i:s');
        $threshold = Carbon::parse($shiftStart)->addMinutes($graceMinutes)->format('H:i:s');

        return $in > $threshold;
    }

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

        $shift  = $rosterShift ?? shifts::default();
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

    protected function countLeaveDays(?Employee $employee): int
    {
        if (! $employee) return 0;

        $count  = 0;
        $leaves = Employeeleaves::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $this->dateTo)
            ->whereDate('end_date', '>=', $this->dateFrom)
            ->get(['start_date', 'end_date']);

        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date)->max($from);
            $end   = Carbon::parse($leave->end_date)->min($to);
            foreach (CarbonPeriod::create($start, $end) as $day) {
                if (! $day->isWeekend()) $count++;
            }
        }

        return $count;
    }

    protected function workingDaysBetween(Carbon $start, Carbon $end): int
    {
        $count  = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) $count++;
            $cursor->addDay();
        }

        return $count;
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        $startDate = Carbon::parse($this->dateFrom);
        $endDate   = Carbon::parse($this->dateTo);
        $dates     = collect(CarbonPeriod::create($startDate, $endDate))->map(fn ($d) => $d->format('Y-m-d'));

        $usersQuery = fpusers::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%'))
            ->orderBy('name');

        $totalUsers = $usersQuery->count();
        $totalPages = (int) ceil($totalUsers / $this->perPage);

        $users = $usersQuery->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->get();

        $userIds     = $users->pluck('fpdevice_id')->toArray();
        $attendances = employeeattendances::query()
            ->whereIn('fpuser_id', $userIds)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->orderBy('clocktime')
            ->get()
            ->groupBy(['fpuser_id', 'clockdate']);

        $scheduledHours = 8 * $dates->count();

        $attendanceMatrix = [];
        foreach ($users as $index => $user) {
            $employee = Employee::where('fpid', $user->fpdevice_id)->first();
            [$shiftStart, $graceMinutes] = $this->resolveShift($employee);

            $row = [
                'index' => ($this->page - 1) * $this->perPage + $index + 1,
                'user'  => $user,
                'dates' => [],
            ];

            $actualHours = 0.0;

            foreach ($dates as $date) {
                $dayAttendance = $attendances[$user->fpdevice_id][$date] ?? collect();

                if ($dayAttendance->isEmpty()) {
                    $row['dates'][$date] = [
                        'status'            => 'no_show',
                        'clock_in'          => null,
                        'clock_out'         => null,
                        'has_checkout'      => false,
                        'attendance_status' => '',
                    ];
                    continue;
                }

                $sorted   = $dayAttendance->sortBy('clocktime');
                $checkIn  = $sorted->first();
                $checkOut = $dayAttendance->count() > 1 ? $sorted->last() : null;

                if ($dayAttendance->count() === 1) {
                    $hour = (int) Carbon::parse($checkIn->clocktime)->format('H');
                    if ($hour >= 12) {
                        $checkOut = $checkIn;
                        $checkIn  = null;
                    }
                }

                $hasCheckout = $checkOut && $checkIn && $checkOut->id !== $checkIn->id;

                if ($hasCheckout) {
                    $actualHours += abs(
                        Carbon::parse($checkIn->clocktime)->floatDiffInHours(Carbon::parse($checkOut->clocktime))
                    );
                }

                $attendStatus = $dayAttendance->pluck('clock_status')->filter()->first();
                if (! $attendStatus) {
                    if ($checkIn && $checkOut && $hasCheckout) {
                        $attendStatus = $this->isLate($checkIn->clocktime, $shiftStart, $graceMinutes) ? 'Late' : 'Present';
                    } else {
                        $attendStatus = 'Incomplete';
                    }
                }

                $row['dates'][$date] = [
                    'status'            => 'present',
                    'clock_in'          => $checkIn?->clocktime,
                    'clock_out'         => $checkOut?->clocktime,
                    'has_checkout'      => $hasCheckout,
                    'attendance_status' => $attendStatus,
                ];
            }

            $row['actual_hours']    = round($actualHours, 1);
            $row['attendance_rate'] = $scheduledHours > 0
                ? round(($actualHours / $scheduledHours) * 100, 1)
                : 0;

            $attendanceMatrix[] = $row;
        }

        return view('livewire.hr.attendance.managefpattendance', [
            'attendanceMatrix' => $attendanceMatrix,
            'dates'            => $dates,
            'totalUsers'       => $totalUsers,
            'totalPages'       => $totalPages,
            'currentPage'      => $this->page,
            'scheduledHours'   => $scheduledHours,
        ]);
    }
}

<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\employeeroster;
use App\Models\shifts;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Per-page size for the day-aggregated listing.
    protected int $perPage = 15;

    // Employee info
    public $employee_id;

    public $fpid;

    public $getfullname;

    public $age;

    public $gender;

    public $email;

    public $photo;

    public $editUrl;

    public $department;

    public $designation;

    public $employeeNumber;

    public $department_id;

    // Shift / late-detection settings used to flag late arrivals.
    public ?string $shiftStart = null;      // e.g. "08:00:00"

    public int $shiftGraceMinutes = 0;      // lateness grace period in minutes

    public ?string $shiftName = null;       // null = no named shift

    public string $shiftSource = 'fallback'; // 'roster' | 'default' | 'fallback'

    // Defaults used when the department has no shift assigned.
    protected string $defaultShiftStart = '08:00:00';

    protected int $defaultGraceMinutes = 15;

    // Form fields
    public $attendance_id;

    public $date;

    public $clock_in;

    public $clock_out;

    public $clock_status;

    // UI state
    public $modalMode = 'create';

    public $showModal = false;

    public $confirmingDelete = null;

    // Filters
    public $search = '';

    public $filterMonth = '';

    public $filterYear = '';

    public $filterStatus = '';

    protected function rules()
    {
        return [
            'date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after:clock_in'],
            'clock_status' => ['required', 'string', 'in:Present,Absent,Late,Half Day,On Leave'],
        ];
    }

    protected $messages = [
        'date.required' => 'Date is required.',
        'clock_status.required' => 'Please select attendance status.',
        'clock_out.after' => 'Clock out time must be after clock in time.',
    ];

    public function mount($id = null)
    {
        $staff = Employee::with(['department', 'designation'])->findOrFail($id);

        $this->employee_id = $id;
        $this->fpid = $staff->fpid;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->photo = $staff->photo;
        $this->editUrl = route('hr.editstaff', $id);
        $this->department = $staff->department?->name;
        $this->designation = $staff->designation?->name;
        $this->employeeNumber = $staff->employee_number ?? $staff->id;
        $this->department_id = $staff->department_id;

        // Default filter to current month/year (set before resolving the shift
        // so the roster lookup is scoped to the month being viewed).
        $this->filterMonth = now()->format('m');
        $this->filterYear = now()->format('Y');

        $this->resolveShift();
    }

    /**
     * Determine the shift start time and lateness grace used for late detection.
     *
     * Checks whether the employee has a roster within the selected month
     * (directly or via their department). If so, that rostered shift is used;
     * otherwise it falls back to the shift flagged as default, then to the
     * hardcoded fallback. count_late is grace minutes.
     */
    protected function resolveShift(): void
    {
        $rosterShift = employeeroster::query()
            ->with('shift')
            ->where(function ($q) {
                $q->where('employee_id', $this->employee_id);

                if ($this->department_id) {
                    $q->orWhere('department_id', $this->department_id);
                }
            })
            ->whereHas('shift')
            ->when($this->filterMonth, fn ($q) => $q->whereMonth('roster_date', $this->filterMonth))
            ->when($this->filterYear, fn ($q) => $q->whereYear('roster_date', $this->filterYear))
            ->latest('roster_date')
            ->latest()
            ->first()?->shift;

        // No roster in this month -> fall back to the configured default shift.
        $shift = $rosterShift ?? shifts::default();
        $this->shiftSource = $rosterShift ? 'roster' : ($shift ? 'default' : 'fallback');

        if ($shift && $shift->start_time) {
            $this->shiftStart = \Carbon\Carbon::parse($shift->start_time)->format('H:i:s');
            $this->shiftGraceMinutes = (int) ($shift->count_late ?? 0);
            $this->shiftName = $shift->name;
        } else {
            $this->shiftStart = $this->defaultShiftStart;
            $this->shiftGraceMinutes = $this->defaultGraceMinutes;
            $this->shiftName = null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        // Re-evaluate which shift applies for the newly selected month.
        $this->resolveShift();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    public function updatedFilterYear()
    {
        $this->resolveShift();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;

        if ($mode === 'edit' && $id) {
            $attendance = employeeattendances::findOrFail($id);
            $this->attendance_id = $id;
            $this->date = $attendance->clockdate;
            $this->clock_in = $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : null;
            $this->clock_out = $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : null;
            $this->clock_status = $attendance->clock_status;
        } else {
            $this->resetForm();
            $this->date = now()->format('Y-m-d');
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['attendance_id', 'date', 'clock_in', 'clock_out', 'clock_status']);
    }

    public function save()
    {
        $this->validate();

        if (! $this->fpid) {
            session()->flash('error', 'Employee does not have a fingerprint ID assigned. Please assign one first.');
            $this->showModal = false;

            return;
        }

        $data = [
            'clockdate' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'clock_status' => $this->clock_status,
        ];

        if ($this->modalMode === 'edit' && $this->attendance_id) {
            $attendance = employeeattendances::findOrFail($this->attendance_id);
            $attendance->update($data);
            session()->flash('success', 'Attendance record updated successfully!');
        } else {
            $data['fpuser_id'] = $this->fpid;
            employeeattendances::create($data);
            session()->flash('success', 'Attendance record added successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->confirmingDelete = $id;
    }

    public function delete()
    {
        if ($this->confirmingDelete) {
            $attendance = employeeattendances::findOrFail($this->confirmingDelete);
            $attendance->delete();
            $this->confirmingDelete = null;
            session()->flash('success', 'Attendance record deleted successfully!');
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus']);
        $this->filterMonth = now()->format('m');
        $this->filterYear = now()->format('Y');

        // Month/year reset to "now" — re-resolve the applicable shift.
        $this->resolveShift();
    }

    /**
     * Pull raw punches for this employee from employeeattendances and collapse
     * them into one aggregated record per day.
     *
     * The device/import data stores one row per punch (clocktimestamp /
     * clockdate / clocktime) with clock_in/clock_out/clock_status usually NULL,
     * so we derive clock in (earliest punch), clock out (latest punch) and a
     * status per day — while still honouring any manually-entered values.
     */
    protected function buildDailyRecords()
    {
        if (! $this->fpid) {
            return collect();
        }

        $query = employeeattendances::where('fpuser_id', $this->fpid);

        if ($this->filterMonth) {
            $query->whereMonth('clockdate', $this->filterMonth);
        }

        if ($this->filterYear) {
            $query->whereYear('clockdate', $this->filterYear);
        }

        $rows = $query->get();

        return $rows
            ->groupBy(fn ($row) => Carbon::parse($row->clockdate)->format('Y-m-d'))
            ->map(fn ($dayRows, $date) => $this->aggregateDay($date, $dayRows))
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Collapse a single day's punches into one record: earliest punch is the
     * clock in, latest is the clock out, with a derived status.
     */
    protected function aggregateDay(string $date, $dayRows): array
    {
        // Prefer explicit clock_in/out (manual entries) and fall back to the
        // earliest/latest raw punch timestamps.
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

        // Worked hours for this day (only when both in and out are known).
        $hours = ($clockIn && $clockOut)
            ? abs(Carbon::parse($clockIn)->floatDiffInHours(Carbon::parse($clockOut)))
            : 0.0;

        // Honour a manually set status, otherwise derive one.
        $status = $dayRows->pluck('clock_status')->filter()->first();

        if (! $status) {
            if ($clockIn && $clockOut) {
                $status = $this->isLate($clockIn) ? 'Late' : 'Present';
            } else {
                $status = 'Incomplete';
            }
        }

        return [
            'id' => $first->id,
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'clock_status' => $status,
            'punches' => $dayRows->count(),
            'hours' => round($hours, 2),
        ];
    }

    /**
     * A clock-in is late when it falls after the shift start time plus the
     * grace period.
     */
    protected function isLate(?string $clockIn): bool
    {
        if (! $clockIn || ! $this->shiftStart) {
            return false;
        }

        $in = Carbon::parse($clockIn);
        $threshold = Carbon::parse($this->shiftStart)->addMinutes($this->shiftGraceMinutes);

        // Compare time-of-day only.
        return $in->format('H:i:s') > $threshold->format('H:i:s');
    }

    public function getAttendanceStats($records = null)
    {
        $records ??= $this->buildDailyRecords();

        // Days with a measurable duration (both clock in and out).
        $workedDays = $records->where('hours', '>', 0);
        $totalHours = $workedDays->sum('hours');
        $avgHours = $workedDays->count() > 0 ? $totalHours / $workedDays->count() : 0;

        // Attendance Rate = (Actual Hours Worked / Scheduled Hours) * 100,
        // where scheduled hours = 8h per working day (Mon–Fri) in the period.
        $scheduledHours = $this->scheduledHours();
        $attendanceRate = $scheduledHours > 0
            ? min(round(($totalHours / $scheduledHours) * 100, 1), 100)
            : 0;

        return [
            'present' => $records->where('clock_status', 'Present')->count(),
            'absent' => $records->where('clock_status', 'Absent')->count(),
            'late' => $records->where('clock_status', 'Late')->count(),
            'incomplete' => $records->where('clock_status', 'Incomplete')->count(),
            'leave' => $records->where('clock_status', 'On Leave')->count(),
            'total' => $records->count(),
            'total_hours' => round($totalHours, 1),
            'avg_hours' => round($avgHours, 1),
            'worked_days' => $workedDays->count(),
            'scheduled_hours' => $scheduledHours,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Scheduled hours for the selected period: 8 hours for each working day
     * (Mon–Fri). For the current month, days are counted only up to today.
     */
    protected function scheduledHours(int $hoursPerDay = 8): int
    {
        $year = $this->filterYear ?: now()->year;

        // Determine the span to count working days over.
        if ($this->filterMonth) {
            $start = Carbon::create((int) $year, (int) $this->filterMonth, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();
        } else {
            // "All Months" -> the whole selected year.
            $start = Carbon::create((int) $year, 1, 1)->startOfYear();
            $end = (clone $start)->endOfYear();
        }

        // Don't count days in the future.
        $today = now();
        if ($end->greaterThan($today)) {
            $end = $today->copy();
        }

        if ($start->greaterThan($end)) {
            return 0;
        }

        $workingDays = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $workingDays++;
            }
            $cursor->addDay();
        }

        return $workingDays * $hoursPerDay;
    }

    public function render()
    {
        $records = $this->buildDailyRecords();

        if ($this->filterStatus) {
            $records = $records->where('clock_status', $this->filterStatus)->values();
        }

        // Stats/summary reflect the current filter selection.
        $stats = $this->getAttendanceStats($records);

        // Paginate the aggregated collection manually.
        $page = $this->getPage();
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $records->forPage($page, $this->perPage)->values(),
            $records->count(),
            $this->perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.hr.staffs.attendance', [
            'attendances' => $attendances,
            'stats' => $stats,
        ]);
    }
}

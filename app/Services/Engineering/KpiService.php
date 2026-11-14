<?php

namespace App\Services\Engineering;

use App\Models\Employee;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\FiveSScore;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\OxygenPlant;
use App\Models\Engineering\OxygenReading;
use App\Models\Engineering\RoomStaff;
use App\Models\Engineering\Section;
use App\Models\Engineering\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Weekly summary and individual KPI figures. Everything is calculated from the same job,
 * feedback and inventory records the MRV dashboard shows: there is no separate data entry
 * apart from 5S scores and oxygen purity readings.
 */
class KpiService
{
    public const GREEN = 'green';

    public const YELLOW = 'yellow';

    public const RED = 'red';

    /* ---------- Reporting week ---------- */

    /**
     * The reporting week containing the date, as UTC instants. The week is reported Monday to
     * Friday; work assigned or finished over a weekend counts towards the week that follows,
     * so the window runs from Saturday 00:00 to Friday 23:59, hospital time.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function week(Carbon|string $date): array
    {
        $timezone = config('engineering.timezone');
        $local = Carbon::parse(Carbon::parse($date)->timezone($timezone)->toDateString(), $timezone);

        // Saturday and Sunday belong to the week that starts on the coming Monday
        $monday = $local->isWeekend() ? $local->copy()->next(Carbon::MONDAY) : $local->copy()->startOfWeek(Carbon::MONDAY);

        return [$monday->copy()->subDays(2)->utc(), $monday->copy()->addDays(4)->endOfDay()->utc()];
    }

    /** The Monday that names the reporting week, as a hospital-local date. */
    public function weekStart(Carbon|string $date): Carbon
    {
        [$from] = $this->week($date);

        return $from->copy()->timezone(config('engineering.timezone'))->addDays(2)->startOfDay();
    }

    /* ---------- Weekly summary ---------- */

    /**
     * Columns of the weekly summary: the whole department, each named section, and the rest
     * as "Other sections" so the figures reconcile.
     *
     * @return array<string, array{label:string, current:array<string, mixed>, previous:?array<string, mixed>}>
     */
    public function weeklySummary(Carbon|string $date): array
    {
        [$from, $to] = $this->week($date);
        [$previousFrom, $previousTo] = $this->week($this->weekStart($date)->subDays(3));

        $order = array_flip(config('engineering.summary_sections'));
        $named = Section::whereIn('code', array_keys($order))->get()->sortBy(fn (Section $section) => $order[$section->code]);
        $namedIds = $named->pluck('id')->all();

        $columns = ['department' => ['label' => 'Department', 'sections' => null]];
        foreach ($named as $section) {
            $columns[$section->code] = ['label' => $section->name, 'sections' => [$section->id]];
        }
        $columns['other'] = ['label' => 'Other sections', 'sections' => Section::whereNotIn('id', $namedIds)->pluck('id')->all()];

        $current = $this->windowJobs($from, $to);
        $previous = $this->windowJobs($previousFrom, $previousTo);
        $open = MaintenanceJob::workable()->get(['id', 'section_id', 'type_of_work']);
        $hasHistory = MaintenanceJob::where('created_at', '<', $from)->exists();

        return collect($columns)->map(function (array $column) use ($current, $previous, $open, $from, $to, $previousFrom, $previousTo, $hasHistory) {
            $inColumn = fn (Collection $jobs) => $column['sections'] === null ? $jobs : $jobs->whereIn('section_id', $column['sections']);

            return [
                'label' => $column['label'],
                'current' => $this->metrics($inColumn($current), $from, $to) + $this->openCounts($inColumn($open)),
                // The first week of operation has nothing to compare against
                'previous' => $hasHistory ? $this->metrics($inColumn($previous), $previousFrom, $previousTo) : null,
            ];
        })->all();
    }

    /**
     * Counts and rates for a set of jobs over a window.
     *
     * @return array<string, int|float|null>
     */
    public function metrics(Collection $jobs, Carbon $from, Carbon $to): array
    {
        $assigned = $jobs->filter(fn (MaintenanceJob $job) => $job->original_assigned_at?->between($from, $to));
        $finished = $jobs->filter(fn (MaintenanceJob $job) => $job->completed_at?->between($from, $to));

        $ppmAssigned = $assigned->filter->isPpm()->count();
        $ppmFinished = $finished->filter->isPpm()->count();

        return [
            'assigned' => $assigned->count(),
            'ppm_assigned' => $ppmAssigned,
            'non_ppm_assigned' => $assigned->count() - $ppmAssigned,
            'finished' => $finished->count(),
            'ppm_finished' => $ppmFinished,
            'non_ppm_finished' => $finished->count() - $ppmFinished,
            'jobs_pct' => $this->percentage($finished->count(), $assigned->count()),
            'ppm_pct' => $this->percentage($ppmFinished, $ppmAssigned),
            'tat_days' => $finished->isEmpty() ? null : round($finished->avg(fn (MaintenanceJob $job) => $job->tatDays()), 2),
            'needing_spares' => $assigned->whereNotNull('spare_required_at')->count(),
            'with_scores' => $finished->filter(fn (MaintenanceJob $job) => $job->currentFeedback !== null)->count(),
            // An unrated finished job counts as 0%
            'satisfaction_pct' => $finished->isEmpty() ? null : round($finished->avg(fn (MaintenanceJob $job) => $job->satisfactionScore()), 1),
        ];
    }

    /** Colour band of a weekly performance figure. */
    public function band(string $metric, int|float|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $limits = config("engineering.weekly_thresholds.{$metric}");

        // Lower is better for turn-around time
        if ($metric === 'tat_days') {
            return match (true) {
                $value <= $limits['green'] => self::GREEN,
                $value <= $limits['yellow'] => self::YELLOW,
                default => self::RED,
            };
        }

        return match (true) {
            $value >= $limits['green'] => self::GREEN,
            $value >= $limits['yellow'] => self::YELLOW,
            default => self::RED,
        };
    }

    /**
     * Improvement of a figure on the previous week: the difference and its colour.
     * Green improved, yellow did not improve, red dropped. A falling TAT is an improvement.
     *
     * @return array{0: ?float, 1: ?string}
     */
    public function variance(string $metric, int|float|null $current, int|float|null $previous): array
    {
        if ($current === null || $previous === null) {
            return [null, null];
        }

        $delta = round($current - $previous, 2);
        $isTat = $metric === 'tat_days';
        $limit = config('engineering.variance_thresholds.'.($isTat ? 'tat_days' : 'pct'));
        $improvement = $isTat ? -$delta : $delta;

        return [$delta, match (true) {
            $isTat ? $improvement >= $limit : $improvement > $limit => self::GREEN,
            $isTat ? $improvement <= -$limit : $improvement < -$limit => self::RED,
            default => self::YELLOW,
        }];
    }

    /* ---------- Individual KPI ---------- */

    /**
     * One row per engineer for the week, with each KPI's raw figure and weighted score.
     * KPIs that do not apply to a person are left out and the remaining weights re-normalised.
     *
     * @param  array<int, string>|null  $sectionIds  null = every section
     * @param  string|null  $onlyEmployeeId  restrict to one person (an engineer viewing their own KPI)
     */
    public function individual(Carbon|string $date, ?array $sectionIds = null, ?string $onlyEmployeeId = null): Collection
    {
        [$from, $to] = $this->week($date);
        $weights = $this->weights();

        $sections = Section::active()->when($sectionIds !== null, fn (Builder $q) => $q->whereIn('id', $sectionIds))->with('staff')->get();
        $jobs = $this->windowJobs($from, $to)->whereNotNull('engineer_id')->groupBy('engineer_id');
        $uptime = $this->uptimeByEmployee();
        $fiveS = FiveSScore::whereDate('week_start', $this->weekStart($date)->toDateString())->pluck('score_pct', 'employee_id');
        $oxygen = $this->oxygenByEmployee($from, $to);

        return $sections->flatMap(fn (Section $section) => $section->staff
            ->when($onlyEmployeeId, fn (Collection $staff) => $staff->where('id', $onlyEmployeeId))
            ->map(function (Employee $employee) use ($section, $jobs, $from, $to, $weights, $uptime, $fiveS, $oxygen) {
                $metrics = $this->metrics($jobs->get($employee->id, collect()), $from, $to);

                $scores = [
                    'jobs' => $metrics['jobs_pct'],
                    'ppm' => $metrics['ppm_pct'],
                    'tat' => $metrics['tat_days'] === null ? null : $this->tatScore($metrics['tat_days'] * 24),
                    'uptime' => $uptime[$employee->id] ?? null,
                    'satisfaction' => $metrics['satisfaction_pct'],
                    'five_s' => isset($fiveS[$employee->id]) ? (float) $fiveS[$employee->id] : null,
                    'oxygen' => $oxygen[$employee->id] ?? null,
                ];

                $weighted = $this->weigh($scores, $weights);

                return [
                    'employee_id' => $employee->id,
                    'name' => trim($employee->first_name.' '.$employee->last_name),
                    'section' => $section->name,
                    'metrics' => $metrics,
                    'scores' => $scores,
                    'weighted' => $weighted,
                    'overall' => $overall = round(array_sum(array_filter($weighted, fn ($value) => $value !== null)), 3),
                    'band' => $this->overallBand($overall),
                ];
            }))
            ->unique('employee_id')
            ->sortBy([['section', 'asc'], ['name', 'asc']])
            ->values();
    }

    /**
     * A person's overall score for each reporting week that starts in the month, and their
     * simple average: the monthly score.
     *
     * @return array{weeks: array<int, array{week_start: Carbon, overall: float, band: string}>, average: ?float}
     */
    public function monthly(string $employeeId, Carbon|string $month): array
    {
        $cursor = Carbon::parse($month)->startOfMonth();
        $end = $cursor->copy()->endOfMonth();
        $monday = $cursor->copy()->startOfWeek(Carbon::MONDAY);
        if ($monday->lt($cursor)) {
            $monday->addWeek();
        }

        $weeks = [];
        for (; $monday->lte($end) && $monday->lte(now()); $monday = $monday->copy()->addWeek()) {
            $row = $this->individual($monday, null, $employeeId)->first();
            if ($row) {
                $weeks[] = ['week_start' => $monday->copy(), 'overall' => $row['overall'], 'band' => $row['band']];
            }
        }

        return [
            'weeks' => $weeks,
            'average' => $weeks === [] ? null : round(collect($weeks)->avg('overall'), 3),
        ];
    }

    /**
     * TAT score: 100% for an immediate fix, falling smoothly through the configured bands to
     * 0% beyond the last band. Interpolated linearly inside a band.
     */
    public function tatScore(float $hours): float
    {
        foreach (config('engineering.kpi.tat_bands') as [$fromHours, $toHours, $scoreStart, $scoreEnd]) {
            if ($hours <= $toHours) {
                $position = max(0, $hours - $fromHours) / ($toHours - $fromHours);

                return round($scoreStart - $position * ($scoreStart - $scoreEnd), 1);
            }
        }

        return 0.0;
    }

    /** red | yellow | green for an overall individual score between 0 and 1. */
    public function overallBand(float $overall): string
    {
        $bands = config('engineering.kpi.overall_bands');

        return match (true) {
            $overall >= $bands['green'] => self::GREEN,
            $overall <= $bands['red'] => self::RED,
            default => self::YELLOW,
        };
    }

    /**
     * @return array<string, float>
     */
    public function weights(): array
    {
        return array_map('floatval', Setting::read('kpi.weights'));
    }

    /**
     * Equipment uptime per device type per department, with the engineer responsible.
     * Uptime = working units / total units.
     */
    public function uptimeDetail(?array $sectionIds = null): Collection
    {
        $responsible = RoomStaff::current()->whereNull('room_id')->with('employee:id,first_name,last_name')->get()->keyBy('department_id');

        return Equipment::inService()
            ->when($sectionIds !== null, fn (Builder $q) => $q->whereIn('section_id', $sectionIds))
            ->with('department:id,name')
            ->get(['id', 'name', 'department_id', 'status', 'quantity'])
            ->groupBy('department_id')
            ->map(function (Collection $items, string $departmentId) use ($responsible) {
                $types = $items->groupBy('name')->map(fn (Collection $units, string $name) => $this->statusCounts($units) + ['type' => $name])->sortKeys()->values();
                $totals = $this->statusCounts($items);

                return [
                    'department' => $items->first()->department->name,
                    'engineer' => ($employee = $responsible->get($departmentId)?->employee) ? trim($employee->first_name.' '.$employee->last_name) : null,
                    'types' => $types,
                    'totals' => $totals,
                ];
            })
            ->sortBy('department')
            ->values();
    }

    /* ---------- Internals ---------- */

    /** Jobs assigned or finished inside the window, with what the metrics need loaded. */
    protected function windowJobs(Carbon $from, Carbon $to): Collection
    {
        return MaintenanceJob::query()
            ->where(fn (Builder $q) => $q
                ->whereBetween('original_assigned_at', [$from, $to])
                ->orWhere(fn (Builder $f) => $f->finished()->whereBetween('completed_at', [$from, $to])))
            ->with('currentFeedback')
            ->get();
    }

    /**
     * @return array{open:int, open_ppm:int, open_non_ppm:int}
     */
    protected function openCounts(Collection $open): array
    {
        $ppm = $open->where('type_of_work', 'ppm')->count();

        return ['open' => $open->count(), 'open_ppm' => $ppm, 'open_non_ppm' => $open->count() - $ppm];
    }

    /** Null (shown as a dash) when there is nothing to divide by. */
    protected function percentage(int $part, int $whole): ?float
    {
        return $whole === 0 ? null : round($part / $whole * 100, 1);
    }

    /**
     * Weighted score per KPI. Scores are capped at 100% and the weights of the KPIs that
     * apply are scaled so they total 100.
     *
     * @param  array<string, ?float>  $scores
     * @param  array<string, float>  $weights
     * @return array<string, ?float>
     */
    protected function weigh(array $scores, array $weights): array
    {
        $applicable = array_sum(array_intersect_key($weights, array_filter($scores, fn ($score) => $score !== null)));

        return collect($scores)->map(fn (?float $score, string $kpi) => $score === null || $applicable <= 0
            ? null
            : round(min($score, 100) / 100 * ($weights[$kpi] / $applicable), 3))->all();
    }

    /**
     * Uptime % of the departments (or rooms) each engineer is currently responsible for.
     *
     * @return array<string, float>
     */
    protected function uptimeByEmployee(): array
    {
        $equipment = Equipment::inService()->get(['id', 'department_id', 'room_id', 'status', 'quantity']);

        return RoomStaff::current()->get()->groupBy('employee_id')
            ->map(function (Collection $assignments) use ($equipment) {
                $units = $assignments->flatMap(fn (RoomStaff $assignment) => $assignment->room_id
                    ? $equipment->where('room_id', $assignment->room_id)
                    : $equipment->where('department_id', $assignment->department_id))->unique('id');

                return $this->statusCounts($units)['uptime'];
            })
            ->filter(fn ($uptime) => $uptime !== null)
            ->all();
    }

    /**
     * @return array{working:int, partly_defective:int, out_of_service:int, total:int, uptime:?float}
     */
    protected function statusCounts(Collection $units): array
    {
        $counts = [
            'working' => (int) $units->where('status', 'working')->sum('quantity'),
            'partly_defective' => (int) $units->where('status', 'partly_defective')->sum('quantity'),
            'out_of_service' => (int) $units->where('status', 'out_of_service')->sum('quantity'),
        ];
        $total = array_sum($counts);

        return $counts + ['total' => $total, 'uptime' => $this->percentage($counts['working'], $total)];
    }

    /**
     * Average purity over the week of the plants each person is responsible for.
     *
     * @return array<string, float>
     */
    protected function oxygenByEmployee(Carbon $from, Carbon $to): array
    {
        $averages = OxygenReading::whereBetween('reading_at', [$from, $to])->get()->groupBy('plant_id')->map->avg('purity_pct');

        return OxygenPlant::where('status', 'active')->whereNotNull('responsible_employee_id')->get()
            ->filter(fn (OxygenPlant $plant) => $averages->has($plant->id))
            ->groupBy('responsible_employee_id')
            ->map(fn (Collection $plants) => round($plants->avg(fn (OxygenPlant $plant) => $averages[$plant->id]), 1))
            ->all();
    }
}

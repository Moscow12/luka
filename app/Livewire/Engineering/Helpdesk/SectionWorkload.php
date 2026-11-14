<?php

namespace App\Livewire\Engineering\Helpdesk;

use App\Enums\Engineering\JobStatus;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Employee;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Open and closed jobs per person in a section, with a drill-down to each person's open jobs.
 */
class SectionWorkload extends Component
{
    use InteractsWithEngineering;

    #[Url]
    public string $sectionId = '';

    #[Url]
    public string $period = 'week';

    #[Url]
    public string $date = '';

    // Staff member whose open jobs are shown
    public ?string $drillEmployeeId = null;

    public function mount(): void
    {
        $sections = EngineeringAccess::selectableSections(Auth::user());
        abort_if($sections->isEmpty() || ! EngineeringAccess::canViewReports(Auth::user()), 403);

        if (! $sections->contains('id', $this->sectionId)) {
            $this->sectionId = $sections->first()->id;
        }

        $this->date = $this->date ?: Eng::now()->toDateString();
    }

    public function updatedSectionId(): void
    {
        // A head of section cannot look into another section
        abort_unless(EngineeringAccess::selectableSections(Auth::user())->contains('id', $this->sectionId), 403);

        $this->drillEmployeeId = null;
    }

    public function drill(?string $employeeId): void
    {
        $this->drillEmployeeId = $this->drillEmployeeId === $employeeId ? null : $employeeId;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function range(): array
    {
        $anchor = Carbon::parse($this->date ?: now());

        return $this->period === 'month'
            ? [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()]
            : [$anchor->copy()->startOfWeek(), $anchor->copy()->endOfWeek()];
    }

    /**
     * One row per staff member: everyone in the section, plus anyone else still holding its jobs.
     */
    protected function rows(): Collection
    {
        [$from, $to] = $this->range();
        $section = Section::findOrFail($this->sectionId);

        $open = MaintenanceJob::where('section_id', $section->id)->workable()->whereNotNull('engineer_id')->get()->groupBy('engineer_id');

        // Local-time period boundaries compared against UTC timestamps
        $closed = MaintenanceJob::where('section_id', $section->id)->finished()
            ->whereBetween('completed_at', [
                Carbon::parse($from->toDateTimeString(), config('engineering.timezone'))->utc(),
                Carbon::parse($to->toDateTimeString(), config('engineering.timezone'))->utc(),
            ])
            ->whereNotNull('engineer_id')->get()->groupBy('engineer_id');

        $staffIds = $section->staff()->pluck('employees.id')->merge($open->keys())->merge($closed->keys())->unique();

        return Employee::withTrashed()->whereIn('id', $staffIds)->orderBy('first_name')->orderBy('last_name')->get()
            ->map(function (Employee $employee) use ($open, $closed) {
                $mine = $open->get($employee->id, collect());
                $done = $closed->get($employee->id, collect());

                return [
                    'id' => $employee->id,
                    'name' => Eng::name($employee),
                    'open' => $mine->count(),
                    'closed' => $done->count(),
                    'avg_tat' => $done->isEmpty() ? null : round($done->avg(fn (MaintenanceJob $job) => $job->tatDays()), 2),
                    'oldest' => $mine->isEmpty() ? null : $mine->max(fn (MaintenanceJob $job) => $job->daysOpen()),
                    'awaiting_spares' => $mine->where('status', JobStatus::AwaitingSpares)->count(),
                ];
            });
    }

    protected function drillJobs(): Collection
    {
        if (! $this->drillEmployeeId) {
            return collect();
        }

        return MaintenanceJob::where('section_id', $this->sectionId)->workable()
            ->where('engineer_id', $this->drillEmployeeId)
            ->with(['requisition.department:id,name', 'requisition.room', 'equipment:id,name,code'])
            ->get()
            ->sortByDesc(fn (MaintenanceJob $job) => $job->daysUnattended())
            ->values();
    }

    public function export()
    {
        [$from, $to] = $this->range();
        $rows = $this->rows();

        return $this->exportTable(
            'Section workload',
            ['S/N', 'Name of staff', 'Open jobs', 'Closed jobs', 'Avg TAT (days)', 'Oldest open job (days)', 'Jobs awaiting spares'],
            $rows->values()->map(fn (array $row, int $i) => [$i + 1, $row['name'], $row['open'], $row['closed'], $row['avg_tat'], $row['oldest'], $row['awaiting_spares']])
                ->push(['', 'SECTION TOTAL', $rows->sum('open'), $rows->sum('closed'), '', '', $rows->sum('awaiting_spares')]),
            ['Section' => Section::find($this->sectionId)?->name, 'Period' => $from->format('d M Y').' - '.$to->format('d M Y')],
        );
    }

    public function render()
    {
        [$from, $to] = $this->range();

        return view('livewire.engineering.helpdesk.section-workload', [
            'sections' => EngineeringAccess::selectableSections(Auth::user()),
            'rows' => $this->rows(),
            'from' => $from,
            'to' => $to,
            'drillJobs' => $this->drillJobs(),
            'drillName' => $this->drillEmployeeId ? Eng::name(Employee::withTrashed()->find($this->drillEmployeeId)) : null,
            'staleDays' => config('engineering.stale_job_days'),
        ]);
    }
}

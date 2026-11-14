<?php

namespace App\Livewire\Engineering\Reports;

use App\Enums\Engineering\JobStatus;
use App\Livewire\Engineering\Concerns\HasReportPeriod;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The master operational register of MRVs. The weekly summary and the individual KPI
 * report are calculated from the same records.
 */
class MrvDashboard extends Component
{
    use HasReportPeriod, InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // An equipment repaired this many times in a month is flagged for management
    public const REPEAT_OFFENDER = 3;

    #[Url]
    public string $filterSection = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(EngineeringAccess::isEngineeringStaff(Auth::user()), 403);
    }

    public function updating(string $name): void
    {
        $this->resetPage();
    }

    /** Requisitions raised in the period, limited to what this user may see. */
    protected function query(): Builder
    {
        [$from, $to] = $this->periodRange();

        return EngineeringAccess::scopeVisibleJobs(MaintenanceJob::query(), Auth::user())
            ->whereHas('requisition', fn (Builder $q) => $q->whereBetween('requested_at', [$from, $to]))
            ->when($this->filterSection, fn (Builder $q, $id) => $q->where('section_id', $id))
            ->when($this->filterStatus, fn (Builder $q, $status) => $q->where('status', $status))
            ->search($this->search)
            ->with([
                'requisition.room', 'requisition.department:id,name', 'equipment:id,name,code,serial_number', 'section:id,name',
                'engineer:id,first_name,last_name', 'assistants:id,first_name,last_name', 'assignedBy:id,first_name,surname',
                'safetyTests', 'sparesRequisitions.lines', 'jobCard', 'notes', 'currentFeedback',
            ])
            ->latest();
    }

    /**
     * Non-PPM requisitions per equipment in the week, month and year containing today.
     *
     * @return Collection<string, array{week:int, month:int, year:int}>
     */
    protected function repeatRepairs(Collection $equipmentIds): Collection
    {
        if ($equipmentIds->isEmpty()) {
            return collect();
        }

        $now = Eng::now();
        $jobs = MaintenanceJob::whereIn('equipment_id', $equipmentIds)
            ->where(fn (Builder $q) => $q->whereNull('type_of_work')->orWhere('type_of_work', '!=', 'ppm'))
            ->where('created_at', '>=', $now->copy()->startOfYear()->utc())
            ->get(['equipment_id', 'created_at']);

        return $jobs->groupBy('equipment_id')->map(fn (Collection $group) => [
            'week' => $group->where('created_at', '>=', $now->copy()->startOfWeek()->utc())->count(),
            'month' => $group->where('created_at', '>=', $now->copy()->startOfMonth()->utc())->count(),
            'year' => $group->count(),
        ]);
    }

    public function export()
    {
        $jobs = $this->query()->get();
        $repeats = $this->repeatRepairs($jobs->pluck('equipment_id')->filter()->unique());
        $yesNo = fn ($value) => $value ? 'Yes' : 'No';

        return $this->exportTable(
            'MRV dashboard report',
            [
                'S/N', 'MRV No.', 'Helpdesk staff', 'Place / floor / room', 'Requesting staff', 'Equipment name', 'Equipment code / serial',
                'Type of work', 'Section required', 'Work description', 'Assigned engineer', 'Assistant engineer', 'Assigned date', 'Assigned time',
                'Requisition date', 'Requisition time', 'Spare required', 'Date requested (spare)', 'Issued date (spare)', 'Job notes',
                'Visual testing', 'Electrical testing', 'Functional test', 'Part requested', 'Procurement order made', 'Client informed',
                'Recommendation', 'Closure date', 'Closure time', 'Requesting staff feedback', 'Final job status', 'Satisfaction', 'Satisfaction score %',
                'TAT (days)', 'Response time (days)', 'Repeat repairs (week / month / year)', 'Re-work count',
            ],
            $jobs->values()->map(function (MaintenanceJob $job, int $i) use ($repeats, $yesNo) {
                $requisition = $job->requisition;
                $tests = $job->safetyTests->keyBy('test_type');
                $lines = $job->sparesRequisitions->flatMap->lines;
                $repeat = $repeats[$job->equipment_id] ?? null;

                return [
                    $i + 1,
                    $job->number,
                    $job->assignedBy?->full_name,
                    $requisition->locationLabel(),
                    $requisition->requester_name,
                    $job->equipmentName(),
                    trim(($job->equipment?->code ?? '').' '.($job->equipment?->serial_number ?? '')),
                    Lookups::label(Lookups::WORK_TYPES, $job->type_of_work, ''),
                    $job->section->name,
                    $job->problem_description,
                    Eng::name($job->engineer),
                    $job->assistants->map(fn ($a) => Eng::name($a))->implode(', '),
                    Eng::dt($job->assigned_at, 'd/m/Y'),
                    Eng::dt($job->assigned_at, 'H:i'),
                    Eng::dt($requisition->requested_at, 'd/m/Y'),
                    Eng::dt($requisition->requested_at, 'H:i'),
                    $yesNo($job->spare_required_at),
                    Eng::dt($job->spare_required_at, 'd/m/Y'),
                    Eng::dt($lines->max('issued_at'), 'd/m/Y'),
                    $job->notes->where('kind', 'engineer')->pluck('body')->implode(' | '),
                    $yesNo($tests->get('visual')?->isSatisfied()),
                    $yesNo($tests->get('electrical')?->isSatisfied()),
                    $yesNo($tests->get('functional')?->isSatisfied()),
                    $lines->pluck('spare_name')->implode(', '),
                    $yesNo($job->procurement_order_at),
                    $yesNo($job->client_informed_at),
                    $job->recommendation,
                    Eng::dt($job->completed_at, 'd/m/Y'),
                    Eng::dt($job->completed_at, 'H:i'),
                    $job->currentFeedback?->comment,
                    Lookups::label(Lookups::FINAL_STATUSES, $job->final_status, $job->status->label()),
                    $job->currentFeedback?->rating->label() ?? 'No score',
                    $job->satisfactionScore(),
                    $job->tatDays(),
                    $job->assigned_at ? round($requisition->requested_at->diffInSeconds($job->original_assigned_at) / 86400, 2) : null,
                    $repeat ? "{$repeat['week']} / {$repeat['month']} / {$repeat['year']}" : '',
                    $job->rework_count,
                ];
            }),
            ['Period' => $this->periodLabel(), 'Section' => Section::find($this->filterSection)?->name, 'Status' => $this->filterStatus, 'Search' => $this->search],
        );
    }

    public function render()
    {
        $jobs = $this->query()->paginate(20);
        $all = (clone $this->query())->setEagerLoads([])->with('currentFeedback')->get();
        $finished = $all->filter(fn (MaintenanceJob $job) => $job->completed_at !== null);

        return view('livewire.engineering.reports.mrv-dashboard', [
            'jobs' => $jobs,
            'repeats' => $this->repeatRepairs($jobs->getCollection()->pluck('equipment_id')->filter()->unique()),
            'totals' => [
                'all' => $all->count(),
                'open' => $all->filter->isOpen()->count(),
                'finished' => $finished->count(),
                'tat' => $finished->isEmpty() ? null : round($finished->avg(fn (MaintenanceJob $job) => $job->tatDays()), 2),
                'satisfaction' => $finished->isEmpty() ? null : round($finished->avg(fn (MaintenanceJob $job) => $job->satisfactionScore()), 1),
                'rework' => $all->where('is_rework', true)->count(),
            ],
            'sections' => EngineeringAccess::selectableSections(Auth::user()),
            'statuses' => JobStatus::options(),
            'periodLabel' => $this->periodLabel(),
        ]);
    }
}

<?php

namespace App\Livewire\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\JobService;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Follow-up page and maintenance tracker: what happened to what I asked for?
 */
class MyRequisitions extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public const TABS = [
        'unattended' => 'Unattended',
        'feedback' => 'Awaiting my feedback',
        'rework' => 'In rework',
        'all' => 'All / history',
    ];

    #[Url]
    public string $tab = 'unattended';

    #[Url]
    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // Follow-up comment
    public ?string $noteJobId = null;

    public string $noteBody = '';

    public function updating(string $name): void
    {
        if (in_array($name, ['tab', 'search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'unattended';
        $this->resetPage();
    }

    public function openNote(string $jobId): void
    {
        $this->resetErrorBag();
        $this->noteJobId = $jobId;
        $this->noteBody = '';
    }

    public function closeNote(): void
    {
        $this->reset(['noteJobId', 'noteBody']);
    }

    public function saveNote(JobService $service): void
    {
        $this->validate(['noteBody' => ['required', 'string', 'min:3', 'max:1000']], [], ['noteBody' => 'comment']);

        $job = $this->baseQuery()->findOrFail($this->noteJobId);

        if ($this->attempt(fn () => $service->addRequesterNote($job, Auth::user(), $this->noteBody), "Comment added to {$job->number}.")) {
            $this->closeNote();
        }
    }

    public function export()
    {
        $jobs = $this->filteredQuery()->get();

        return $this->exportTable(
            'My requisitions - '.self::TABS[$this->tab],
            ['S/N', 'MRV No.', 'Requested', 'Equipment', 'Reported by', 'Department', 'Floor / room / place', 'Priority', 'Status', 'Days open', 'Received (assigned)', 'Assigned to', 'Phone', 'Lead time (days)', 'Spare ordered', 'Job complete'],
            $jobs->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number,
                Eng::dt($job->requisition->requested_at),
                $job->equipmentName(),
                $job->requisition->requester_name,
                $job->requisition->department?->name,
                $job->requisition->locationLabel(),
                ucfirst($job->requisition->priority),
                $job->status->label().($job->is_rework ? ' (RE-WORK)' : ''),
                $job->daysOpen(),
                Eng::dt($job->assigned_at),
                Eng::name($job->engineer),
                $job->engineer?->phone,
                $job->days_to_be_done,
                $job->spare_required_at ? 'Yes' : 'No',
                $job->isOpen() ? 'No' : 'Yes',
            ]),
            ['View' => self::TABS[$this->tab], 'Search' => $this->search, 'From' => $this->dateFrom, 'To' => $this->dateTo],
        );
    }

    /** Own requisitions, or the whole department's when the hospital is configured that way. */
    protected function baseQuery(): Builder
    {
        $user = Auth::user();
        $departmentId = config('engineering.requester_scope') === 'department'
            ? EngineeringAccess::employee($user)?->department_id
            : null;

        return MaintenanceJob::query()->whereHas('requisition', fn (Builder $q) => $q
            ->where('requested_by', $user->id)
            ->when($departmentId, fn (Builder $d) => $d->orWhere('department_id', $departmentId)));
    }

    protected function applyTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'unattended' => $query->open(),
            'feedback' => $query->awaitingFeedback(),
            'rework' => $query->where('status', JobStatus::Rework->value),
            default => $query,
        };
    }

    protected function filteredQuery(): Builder
    {
        return $this->applyTab($this->baseQuery(), $this->tab)
            ->with(['requisition.department:id,name', 'requisition.room', 'equipment:id,name,code', 'engineer:id,first_name,last_name,phone', 'currentFeedback'])
            ->search($this->search)
            ->when($this->tab === 'all' && $this->dateFrom, fn (Builder $q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->tab === 'all' && $this->dateTo, fn (Builder $q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->emergencyFirst()
            ->latest();
    }

    public function render()
    {
        return view('livewire.engineering.my-requisitions', [
            'jobs' => $this->filteredQuery()->paginate(15),
            'counts' => collect(self::TABS)->except('all')->map(fn ($label, $tab) => $this->applyTab($this->baseQuery(), $tab)->count()),
            'canAssign' => EngineeringAccess::canAssign(Auth::user()),
            'staleDays' => config('engineering.stale_job_days'),
        ]);
    }
}

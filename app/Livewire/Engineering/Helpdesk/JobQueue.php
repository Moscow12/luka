<?php

namespace App\Livewire\Engineering\Helpdesk;

use App\Enums\Engineering\JobStatus;
use App\Livewire\Engineering\Concerns\HasAssignmentForm;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\JobService;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Job collection & assignment: every submitted requisition lands here, emergencies first.
 */
class JobQueue extends Component
{
    use HasAssignmentForm, InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Carried in from "Assign job" on the follow-up page
    #[Url]
    public ?string $job = null;

    #[Url]
    public string $filterSection = '';

    #[Url]
    public string $filterPriority = '';

    public string $filterDepartment = '';

    // "waiting" = not yet assigned; "open" also lists jobs already with an engineer
    public string $filterStatus = 'waiting';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $search = '';

    // Priority correction inside the panel
    public string $newPriority = '';

    public string $priorityReason = '';

    public function mount(): void
    {
        abort_unless(EngineeringAccess::canAssign(Auth::user()), 403);

        if ($this->job) {
            $this->process($this->job);
        }
    }

    public function updating(string $name): void
    {
        if (str_starts_with($name, 'filter') || in_array($name, ['dateFrom', 'dateTo', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['filterSection', 'filterPriority', 'filterDepartment', 'filterStatus', 'dateFrom', 'dateTo', 'search']);
        $this->resetPage();
    }

    /** Opens the assignment panel for a job waiting in the queue. */
    public function process(string $jobId): void
    {
        $job = $this->assignableJobs()->with('requisition')->find($jobId);

        if (! $job || $job->status !== JobStatus::Submitted) {
            session()->flash('error', 'That job is no longer waiting for assignment.');
            $this->job = null;

            return;
        }

        $this->loadAssignmentForm($job);
        $this->newPriority = $job->requisition->priority;
        $this->priorityReason = '';
    }

    public function closePanel(): void
    {
        $this->closeAssignmentForm();
        $this->reset(['job', 'newPriority', 'priorityReason']);
    }

    public function reclassify(JobService $service): void
    {
        $this->validate([
            'newPriority' => ['required', Rule::in(array_keys(Lookups::PRIORITIES))],
            'priorityReason' => ['required', 'string', 'min:5', 'max:500'],
        ], [], ['priorityReason' => 'reason']);

        $job = $this->assignableJobs()->with('requisition')->findOrFail($this->formJobId);

        if ($this->attempt(fn () => $service->reclassify($job->requisition, Auth::user(), $this->newPriority, $this->priorityReason), 'Priority updated.')) {
            $this->priorityReason = '';
            $this->daysToBeDone = config('engineering.lead_time_days.'.$this->newPriority);
        }
    }

    public function assign(JobService $service): void
    {
        $this->validate([
            'typeOfWork' => ['required', Rule::in(array_keys(Lookups::WORK_TYPES))],
            'formSectionId' => ['required'],
            'engineerId' => ['required'],
            'assistantIds' => ['array'],
            'daysToBeDone' => ['required', 'integer', 'min:1', 'max:365'],
            'assignmentNote' => ['nullable', 'string', 'max:1000'],
        ], [
            'formSectionId.required' => 'Select the section required.',
            'engineerId.required' => 'Select the engineer to assign.',
        ]);

        $job = $this->assignableJobs()->findOrFail($this->formJobId);

        $assigned = $this->attempt(fn () => $service->assign($job, Auth::user(), [
            'type_of_work' => $this->typeOfWork,
            'section_id' => $this->formSectionId,
            'engineer_id' => $this->engineerId,
            'assistant_ids' => $this->assistantIds,
            'days_to_be_done' => $this->daysToBeDone,
            'note' => $this->assignmentNote ?: null,
        ]), "{$job->number} assigned. The engineer has been notified.");

        if ($assigned) {
            $this->closePanel();
        }
    }

    public function export()
    {
        return $this->exportTable(
            'Job collection and assignment',
            ['S/N', 'MRV No.', 'Requested', 'Priority', 'Equipment', 'ID code / serial', 'Description', 'Reported by', 'Phone', 'Department', 'Floor / room / place', 'Section', 'Status', 'Waiting'],
            $this->queueQuery()->get()->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number,
                Eng::dt($job->requisition->requested_at),
                ucfirst($job->requisition->priority),
                $job->equipmentName(),
                trim($job->equipmentCode().' '.$job->equipment?->serial_number),
                $job->problem_description,
                $job->requisition->requester_name,
                $job->requisition->requester_phone,
                $job->requisition->department?->name,
                $job->requisition->locationLabel(),
                $job->section->name,
                $job->status->label(),
                Eng::elapsed($job->requisition->requested_at),
            ]),
            ['Section' => Section::find($this->filterSection)?->name, 'Priority' => $this->filterPriority, 'From' => $this->dateFrom, 'To' => $this->dateTo, 'Search' => $this->search],
        );
    }

    protected function queueQuery(): Builder
    {
        return $this->assignableJobs()
            ->with(['requisition.department:id,name', 'requisition.room', 'requisition.category:id,name', 'equipment:id,name,code,serial_number', 'section:id,name', 'engineer:id,first_name,last_name'])
            ->when($this->filterStatus === 'waiting', fn (Builder $q) => $q->where('status', JobStatus::Submitted->value), fn (Builder $q) => $q->open())
            ->when($this->filterSection, fn (Builder $q, $id) => $q->where('section_id', $id))
            ->whereHas('requisition', fn (Builder $q) => $q
                ->when($this->filterPriority, fn (Builder $r, $priority) => $r->where('priority', $priority))
                ->when($this->filterDepartment, fn (Builder $r, $id) => $r->where('department_id', $id))
                ->when($this->dateFrom, fn (Builder $r, $date) => $r->whereDate('requested_at', '>=', $date))
                ->when($this->dateTo, fn (Builder $r, $date) => $r->whereDate('requested_at', '<=', $date)))
            ->search($this->search)
            ->emergencyFirst()
            ->oldest();
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.engineering.helpdesk.job-queue', [
            'jobs' => $this->queueQuery()->paginate(15),
            'waiting' => $this->assignableJobs()->where('status', JobStatus::Submitted->value)->count(),
            'emergencies' => $this->assignableJobs()->where('status', JobStatus::Submitted->value)
                ->whereHas('requisition', fn (Builder $q) => $q->where('priority', 'emergency'))->count(),
            'sections' => EngineeringAccess::selectableSections($user),
            'allSections' => Section::active()->get(),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'panelJob' => $this->formJobId
                ? MaintenanceJob::with(['requisition.department', 'requisition.room', 'requisition.category', 'equipment'])->find($this->formJobId)
                : null,
            'staff' => $this->sectionStaff(),
        ]);
    }
}

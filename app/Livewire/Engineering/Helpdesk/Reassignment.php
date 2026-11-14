<?php

namespace App\Livewire\Engineering\Helpdesk;

use App\Livewire\Engineering\Concerns\HasAssignmentForm;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Employee;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\JobService;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Move an open job to another engineer when the first assignee cannot attend it.
 */
class Reassignment extends Component
{
    use HasAssignmentForm, InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $filterSection = '';

    #[Url]
    public string $filterEngineer = '';

    public string $search = '';

    public string $reason = '';

    public bool $keepClock = true;

    public function mount(): void
    {
        abort_unless(EngineeringAccess::canAssign(Auth::user()), 403);
    }

    public function updating(string $name): void
    {
        if (str_starts_with($name, 'filter') || $name === 'search') {
            $this->resetPage();
        }
    }

    public function open(string $jobId): void
    {
        $this->loadAssignmentForm($this->assignableJobs()->workable()->with('requisition')->findOrFail($jobId));
        $this->reason = '';
        $this->keepClock = true;
    }

    public function reassign(JobService $service): void
    {
        $this->validate([
            'formSectionId' => ['required'],
            'engineerId' => ['required'],
            'assistantIds' => ['array'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'formSectionId.required' => 'Select the section required.',
            'engineerId.required' => 'Select the new engineer.',
        ]);

        $job = $this->assignableJobs()->findOrFail($this->formJobId);

        $moved = $this->attempt(fn () => $service->reassign($job, Auth::user(), [
            'section_id' => $this->formSectionId,
            'engineer_id' => $this->engineerId,
            'assistant_ids' => $this->assistantIds,
            'reason' => $this->reason,
            'keep_clock' => $this->keepClock,
        ]), "{$job->number} re-assigned. Both engineers have been notified.");

        if ($moved) {
            $this->closeAssignmentForm();
        }
    }

    public function export()
    {
        return $this->exportTable(
            'Job re-assignment',
            ['S/N', 'MRV No.', 'Requested', 'Equipment', 'Reported by', 'Department', 'Floor / room / place', 'Assigned engineer', 'Required section', 'Status', 'Days open'],
            $this->jobsQuery()->get()->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number,
                Eng::dt($job->requisition->requested_at),
                $job->equipmentName(),
                $job->requisition->requester_name,
                $job->requisition->department?->name,
                $job->requisition->locationLabel(),
                Eng::name($job->engineer),
                $job->section->name,
                $job->status->label(),
                $job->daysOpen(),
            ]),
            ['Section' => Section::find($this->filterSection)?->name, 'Search' => $this->search],
        );
    }

    protected function jobsQuery(): Builder
    {
        return $this->assignableJobs()
            ->workable()
            ->with(['requisition.department:id,name', 'requisition.room', 'equipment:id,name,code', 'engineer:id,first_name,last_name', 'section:id,name'])
            ->when($this->filterSection, fn (Builder $q, $id) => $q->where('section_id', $id))
            ->when($this->filterEngineer, fn (Builder $q, $id) => $q->where('engineer_id', $id))
            ->search($this->search)
            ->emergencyFirst()
            ->oldest('assigned_at');
    }

    public function render()
    {
        $engineerIds = $this->assignableJobs()->workable()->whereNotNull('engineer_id')->distinct()->pluck('engineer_id');

        return view('livewire.engineering.helpdesk.reassignment', [
            'jobs' => $this->jobsQuery()->paginate(15),
            'sections' => EngineeringAccess::selectableSections(Auth::user()),
            'allSections' => Section::active()->get(),
            'engineers' => Employee::whereIn('id', $engineerIds)->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'panelJob' => $this->formJobId ? MaintenanceJob::with(['engineer', 'section', 'requisition'])->find($this->formJobId) : null,
            'staff' => $this->sectionStaff(),
        ]);
    }
}

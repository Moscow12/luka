<?php

namespace App\Livewire\Engineering\Concerns;

use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The assignment panel shared by job assignment and re-assignment: pick a section, then an
 * engineer and assistants from that section only.
 */
trait HasAssignmentForm
{
    public ?string $formJobId = null;

    public string $typeOfWork = 'breakdown';

    public ?string $formSectionId = null;

    public ?string $engineerId = null;

    /** @var array<int, string> */
    public array $assistantIds = [];

    public int $daysToBeDone = 2;

    public string $assignmentNote = '';

    /** Changing the section clears people who do not belong to it. */
    public function updatedFormSectionId(): void
    {
        $this->engineerId = null;
        $this->assistantIds = [];
    }

    /** The assigned engineer cannot also be an assistant. */
    public function updatedEngineerId(): void
    {
        $this->assistantIds = array_values(array_diff($this->assistantIds, [$this->engineerId]));
    }

    public function closeAssignmentForm(): void
    {
        $this->reset(['formJobId', 'formSectionId', 'engineerId', 'assistantIds', 'assignmentNote']);
        $this->resetErrorBag();
    }

    protected function loadAssignmentForm(MaintenanceJob $job): void
    {
        $this->resetErrorBag();

        $this->formJobId = $job->id;
        $this->formSectionId = $job->section_id;
        $this->typeOfWork = $job->type_of_work ?? 'breakdown';
        $this->engineerId = null;
        $this->assistantIds = [];
        $this->daysToBeDone = $job->days_to_be_done ?? config('engineering.lead_time_days.'.$job->requisition->priority);
        $this->assignmentNote = '';
    }

    /** Active staff of the selected section: the only people offered in the drop-downs. */
    protected function sectionStaff(): Collection
    {
        return $this->formSectionId
            ? Section::find($this->formSectionId)?->assignableStaff()->get(['employees.id', 'first_name', 'last_name']) ?? collect()
            : collect();
    }

    /** Jobs in the sections this user may assign for. */
    protected function assignableJobs(): Builder
    {
        $sectionIds = EngineeringAccess::supervisedSectionIds(Auth::user());

        return MaintenanceJob::query()
            ->when($sectionIds !== null, fn (Builder $q) => $q->whereIn('section_id', $sectionIds));
    }
}

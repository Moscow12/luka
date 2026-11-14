<?php

namespace App\Livewire\Engineering\Jobs;

use App\Models\Engineering\MaintenanceJob;
use App\Services\Engineering\EngineeringAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * One-page, read-only summary of an MRV item: printable for the equipment file.
 */
class Summary extends Component
{
    public string $jobId;

    public function mount(MaintenanceJob $job): void
    {
        abort_unless(EngineeringAccess::canViewSummary(Auth::user(), $job), 403);

        $this->jobId = $job->id;
    }

    public function render()
    {
        $user = Auth::user();
        $job = MaintenanceJob::with([
            'requisition.department', 'requisition.room', 'requisition.category', 'equipment', 'section',
            'engineer', 'assistants', 'assignedBy', 'notes.author', 'safetyTests.completedBy',
            'sparesRequisitions.lines', 'jobCard', 'rca', 'oxygenOrder.deliveredBy', 'currentFeedback', 'signedOutBy',
        ])->findOrFail($this->jobId);

        return view('livewire.engineering.jobs.summary', [
            'job' => $job,
            'canOpenWorkspace' => EngineeringAccess::canViewJob($user, $job),
            // The requester sees the engineer's recommendation but not internal job notes
            'showNotes' => EngineeringAccess::canViewJob($user, $job),
        ]);
    }
}

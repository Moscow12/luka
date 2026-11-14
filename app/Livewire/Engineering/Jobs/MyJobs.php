<?php

namespace App\Livewire\Engineering\Jobs;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The logged-in engineer's own work, and nobody else's.
 */
class MyJobs extends Component
{
    use InteractsWithEngineering;

    #[Url]
    public string $tab = 'open';

    #[Url]
    public string $search = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'finished' ? 'finished' : 'open';
    }

    protected function baseQuery(): Builder
    {
        return EngineeringAccess::scopeOwnJobs(MaintenanceJob::query(), Auth::user());
    }

    /** Emergencies and overdue jobs first, then the longest-waiting. */
    protected function jobs(): Collection
    {
        $query = $this->baseQuery()
            ->with(['requisition.room', 'requisition.department:id,name', 'equipment:id,name,code', 'engineer:id,first_name,last_name', 'section:id,name', 'oxygenOrder', 'currentFeedback'])
            ->search($this->search);

        if ($this->tab === 'finished') {
            return $query->finished()->latest('completed_at')->limit(100)->get();
        }

        return $query->workable()->get()
            ->sortBy(fn (MaintenanceJob $job) => [
                $job->requisition->isEmergency() ? 0 : 1,
                $job->isOverdue() ? 0 : 1,
                $job->assigned_at?->timestamp ?? 0,
            ])
            ->values();
    }

    public function export()
    {
        return $this->exportTable(
            'My assigned jobs',
            ['SN', 'MRV #', 'Requested date', 'Requested staff', 'Administrative resp.', 'Equipment name', 'Floor / location', 'Assigned engineer', 'Oxygen delivered by', 'Assigned section', 'No. of days', 'Days allowed', 'Status'],
            $this->jobs()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number,
                Eng::dt($job->requisition->requested_at),
                $job->requisition->requester_name,
                $job->requisition->requester_role,
                $job->equipmentName(),
                $job->requisition->locationLabel(),
                Eng::name($job->engineer),
                $job->oxygenOrder?->isDelivered() ? Eng::name($job->oxygenOrder->deliveredBy) : '',
                $job->section->name,
                $job->daysOpen(),
                $job->days_to_be_done,
                $job->status->label(),
            ]),
            ['View' => ucfirst($this->tab), 'Search' => $this->search],
        );
    }

    public function render()
    {
        $open = $this->baseQuery()->workable()->get();

        return view('livewire.engineering.jobs.my-jobs', [
            'jobs' => $this->jobs(),
            'openCount' => $open->count(),
            'overdueCount' => $open->filter->isOverdue()->count(),
            'employee' => EngineeringAccess::employee(Auth::user()),
        ]);
    }
}

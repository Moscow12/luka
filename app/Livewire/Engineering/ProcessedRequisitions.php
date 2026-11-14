<?php

namespace App\Livewire\Engineering;

use App\Enums\Engineering\SatisfactionRating;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
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
 * Finished work waiting for, or already carrying, the requester's satisfaction rating.
 */
class ProcessedRequisitions extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    // Job being rated; can be carried in from the "Job complete" alert
    #[Url]
    public ?string $rate = null;

    public string $rating = '';

    public string $comment = '';

    // Job offered for rework after a poor rating or a wrong closure
    public ?string $reworkJobId = null;

    public string $reworkReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openRating(string $jobId): void
    {
        $this->resetErrorBag();
        $this->rate = $jobId;
        $this->reset(['rating', 'comment']);
    }

    public function closeRating(): void
    {
        $this->reset(['rate', 'rating', 'comment']);
    }

    public function saveFeedback(JobService $service): void
    {
        $this->validate([
            'rating' => ['required', Rule::enum(SatisfactionRating::class)],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], ['rating.required' => 'Choose how satisfied you are with the solution.']);

        $job = $this->baseQuery()->findOrFail($this->rate);
        $rating = SatisfactionRating::from($this->rating);

        if (! $this->attempt(fn () => $service->giveFeedback($job, Auth::user(), $rating, $this->comment ?: null), "Thank you. Your feedback on {$job->number} has been saved.")) {
            return;
        }

        $comment = $this->comment;
        $this->closeRating();

        // A poor rating offers to send the job straight back to the same engineer
        if ($rating === SatisfactionRating::PoorService) {
            $this->reworkJobId = $job->id;
            $this->reworkReason = $comment;
        }
    }

    public function openRework(string $jobId): void
    {
        $this->resetErrorBag();
        $this->reworkJobId = $jobId;
        $this->reworkReason = '';
    }

    public function closeRework(): void
    {
        $this->reset(['reworkJobId', 'reworkReason']);
    }

    public function resend(JobService $service): void
    {
        $this->validate(['reworkReason' => ['required', 'string', 'min:5', 'max:1000']], [], ['reworkReason' => 'reason']);

        $job = $this->baseQuery()->findOrFail($this->reworkJobId);

        if ($this->attempt(fn () => $service->rework($job, Auth::user(), $this->reworkReason), "{$job->number} has been sent back to engineering for rework.")) {
            $this->closeRework();
        }
    }

    public function export()
    {
        $jobs = $this->filteredQuery()->get();

        return $this->exportTable(
            'Processed requisitions',
            ['S/N', 'MRV No.', 'Requested', 'Equipment', 'Reported by', 'Department', 'Floor / room / place', 'Engineer', 'Completed on', 'TAT (days)', 'Final job status', 'My rating', 'Score %', 'Comment'],
            $jobs->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number.($job->is_rework ? ' (RE-WORK)' : ''),
                Eng::dt($job->requisition->requested_at),
                $job->equipmentName(),
                $job->requisition->requester_name,
                $job->requisition->department?->name,
                $job->requisition->locationLabel(),
                Eng::name($job->engineer),
                Eng::dt($job->completed_at),
                $job->tatDays(),
                Lookups::label(Lookups::FINAL_STATUSES, $job->final_status),
                $job->currentFeedback?->rating->label() ?? 'No score',
                $job->satisfactionScore(),
                $job->currentFeedback?->comment,
            ]),
            ['Search' => $this->search],
        );
    }

    protected function baseQuery(): Builder
    {
        return MaintenanceJob::query()
            ->finished()
            ->whereHas('requisition', fn (Builder $q) => $q->where('requested_by', Auth::id()));
    }

    protected function filteredQuery(): Builder
    {
        return $this->baseQuery()
            ->with(['requisition.department:id,name', 'requisition.room', 'equipment:id,name,code', 'engineer:id,first_name,last_name', 'assistants:id,first_name,last_name', 'section:id,name', 'currentFeedback'])
            ->search($this->search)
            ->latest('completed_at');
    }

    public function render()
    {
        return view('livewire.engineering.processed-requisitions', [
            'jobs' => $this->filteredQuery()->paginate(15),
            'ratingJob' => $this->rate ? $this->filteredQuery()->find($this->rate) : null,
            'reworkJob' => $this->reworkJobId ? $this->baseQuery()->with('engineer')->find($this->reworkJobId) : null,
            'ratings' => SatisfactionRating::cases(),
        ]);
    }
}

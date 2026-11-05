<?php

namespace App\Livewire\Performance\Supervisor;

use App\Models\EmployeePlanImplementation;
use App\Services\Performance\EvaluationWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VerifyImplementations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $filterStatus = 'pending';

    #[Url]
    public $employeeFilter = '';

    public $search = '';

    #[Locked]
    public $reviewingEntryId = null;

    #[Locked]
    public $reviewDecision = 'verified';

    public $reviewScore = null;

    public $reviewComment = '';

    public function mount()
    {
        abort_unless(EvaluationWorkflow::canVerifyAny(Auth::user()), 403);
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearEmployeeFilter()
    {
        $this->employeeFilter = '';
        $this->resetPage();
    }

    private function entries(): Builder
    {
        return EvaluationWorkflow::verifiableEntries(Auth::user());
    }

    /** Ids come from the client, so every action looks the entry up within what this user may verify. */
    private function findEntry($entryId): EmployeePlanImplementation
    {
        return $this->entries()->with('employeePlanItem.employeePlan')->findOrFail($entryId);
    }

    /** Once an evaluation is approved its score is final, so the work behind it stops changing. */
    private function isLocked(EmployeePlanImplementation $entry): bool
    {
        return (bool) $entry->employeePlanItem?->employeePlan?->hasApprovedEvaluation();
    }

    public function openReview($entryId, $decision)
    {
        abort_unless(in_array($decision, ['verified', 'rejected'], true), 404);

        $entry = $this->findEntry($entryId);

        $this->resetErrorBag();
        $this->reviewingEntryId = $entry->id;
        $this->reviewDecision = $decision;
        $this->reviewScore = $entry->status === $decision ? $entry->verification_score : null;
        $this->reviewComment = $entry->status === $decision ? (string) $entry->supervisor_remarks : '';
    }

    public function closeReview()
    {
        $this->reviewingEntryId = null;
        $this->reviewDecision = 'verified';
        $this->reviewScore = null;
        $this->reviewComment = '';
        $this->resetErrorBag();
    }

    public function saveReview()
    {
        $rejecting = $this->reviewDecision === 'rejected';

        $this->validate([
            'reviewScore' => ['nullable', 'integer', 'min:1', 'max:10'],
            'reviewComment' => $rejecting ? ['required', 'string', 'min:5', 'max:1000'] : ['nullable', 'string', 'max:1000'],
        ], [
            'reviewComment.required' => 'Please tell the employee why this entry is rejected.',
        ]);

        $entry = $this->findEntry($this->reviewingEntryId);

        if ($this->isLocked($entry)) {
            session()->flash('error', 'This entry belongs to an approved evaluation and can no longer be changed.');
            $this->closeReview();

            return;
        }

        $entry->update([
            'status' => $this->reviewDecision,
            // A rejected entry doesn't count, so it carries no score
            'verification_score' => $rejecting || $this->reviewScore === '' ? null : $this->reviewScore,
            'supervisor_remarks' => $this->reviewComment !== '' ? $this->reviewComment : null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        session()->flash('success', $rejecting
            ? 'Entry rejected. The employee can correct it and it will come back for verification.'
            : 'Entry verified. It now counts toward the score.');
        $this->closeReview();
    }

    public function render()
    {
        $entries = $this->entries()
            ->when($this->filterStatus, fn (Builder $q) => $q->where('status', $this->filterStatus))
            ->when($this->employeeFilter, fn (Builder $q) => $q->where('employee_id', $this->employeeFilter))
            ->when(trim($this->search) !== '', function (Builder $q) {
                $term = '%'.trim($this->search).'%';

                $q->where(function (Builder $q) use ($term) {
                    $q->where('activity_title', 'like', $term)
                        ->orWhereHas('employee', fn (Builder $employee) => $employee->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('employee_no', 'like', $term));
                });
            })
            ->with(['employee.department', 'employeePlanItem.employeePlan'])
            ->orderBy('implementation_date', 'desc')
            ->paginate(15);

        return view('livewire.performance.supervisor.verify-implementations', [
            'entries' => $entries,
            'pendingCount' => $this->entries()->where('status', 'pending')->count(),
        ]);
    }
}

<?php

namespace App\Livewire\Hr\Leave\Concerns;

use App\Models\Employeeleaves;
use App\Services\Leave\LeaveApprovalChain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The "View" approval-progress modal shared by the leave pages.
 *
 * A component says which leave requests the viewer may open through leaveScope(); every
 * lookup goes through it, so a request outside that scope is never found.
 */
trait ShowsLeaveProgress
{
    public $showProgressModal = false;

    public $progressLeaveId = null;

    /** Leave requests this viewer may open. */
    abstract protected function leaveScope(): Builder;

    public function viewProgress($id): void
    {
        $this->progressLeaveId = $this->leaveScope()->whereKey($id)->firstOrFail()->id;
        $this->showProgressModal = true;
    }

    public function closeProgressModal(): void
    {
        $this->showProgressModal = false;
        $this->progressLeaveId = null;
    }

    /** Relations the progress timeline reads; eager-load them on any list passed to withProgress(). */
    protected function progressRelations(): array
    {
        return ['leave', 'employee', 'approvalnote.approval_level', 'approvalnote.approver'];
    }

    /** Attach ->progress (LeaveApprovalChain::progress()) to each leave. */
    protected function withProgress(Collection $leaves): Collection
    {
        $chain = app(LeaveApprovalChain::class);

        return $leaves->each(fn (Employeeleaves $leave) => $leave->progress = $chain->progress($leave));
    }

    /**
     * The leave open in the modal, taken from the listed leaves when it is there, otherwise
     * loaded directly (a search filter may be hiding it).
     */
    protected function progressLeave(Collection $listed): ?Employeeleaves
    {
        if (! $this->showProgressModal || ! $this->progressLeaveId) {
            return null;
        }

        $leave = $listed->firstWhere('id', $this->progressLeaveId)
            ?? $this->leaveScope()->whereKey($this->progressLeaveId)->with($this->progressRelations())->first();

        if ($leave && ! isset($leave->progress)) {
            $leave->progress = app(LeaveApprovalChain::class)->progress($leave);
        }

        return $leave;
    }
}

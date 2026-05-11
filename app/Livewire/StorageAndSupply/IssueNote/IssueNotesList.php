<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\{IssueNote, UserApproval};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;

class IssueNotesList extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = 'pending';
    public $selectedIssueNote = null;
    public $showPreviewModal = false;
    public $canApprove = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->checkApprovalPermission();
    }

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $issueNotes = IssueNote::with([
                'store_requisition',
                'dept_requesting',
                'dept_issuing',
                'issuer',
                'preparer',
                'approver',
                'issue_note_items.item',
                'issue_note_items.batch'
            ])
            ->when($selectedSubdepartmentId, function ($query) use ($selectedSubdepartmentId) {
                $query->where(function ($q) use ($selectedSubdepartmentId) {
                    $q->where('dept_issuing_id', $selectedSubdepartmentId)
                      ->orWhere('dept_requesting_id', $selectedSubdepartmentId);
                });
            })
            ->when($this->filter_status !== 'all', function ($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('issue_number', 'like', "%{$this->search}%")
                      ->orWhere('voucher_number', 'like', "%{$this->search}%")
                      ->orWhereHas('dept_requesting', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      )
                      ->orWhereHas('dept_issuing', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.storage-and-supply.issue-note.issue-notes-list', [
            'issueNotes' => $issueNotes,
        ]);
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'issue_note');
        })->where('user_id', auth()->id())->exists();
    }

    public function viewIssueNote($id)
    {
        $this->selectedIssueNote = IssueNote::with([
            'issue_note_items.item',
            'issue_note_items.batch',
            'store_requisition',
            'dept_requesting',
            'dept_issuing',
            'issuer',
            'preparer',
            'approver'
        ])->find($id);

        if ($this->selectedIssueNote) {
            $this->showPreviewModal = true;
            $this->dispatch('modal-show', 'issueNotePreviewModal');
        } else {
            $this->dispatch('error', 'Issue Note not found.');
        }
    }

    public function closeModal()
    {
        $this->showPreviewModal = false;
        $this->selectedIssueNote = null;
        $this->dispatch('modal-hide', 'issueNotePreviewModal');
    }

    public function approveIssueNote()
    {
        if (!$this->selectedIssueNote) {
            return $this->dispatch('error', 'Issue Note not found.');
        }

        if (!$this->selectedIssueNote->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($this->selectedIssueNote->status !== 'pending') {
            return $this->dispatch('error', 'Only pending Issue Notes can be approved.');
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $this->selectedIssueNote->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedIssueNote->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $this->selectedIssueNote->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $this->selectedIssueNote->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $this->selectedIssueNote->current_approval_level = $nextLevel->label;
                $this->selectedIssueNote->save();

                $approvers = $this->selectedIssueNote->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "Issue Note moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - update stock ledger
                $this->selectedIssueNote->status = 'approved';
                $this->selectedIssueNote->approved_by = auth()->id();
                $this->selectedIssueNote->approved_at = now();
                $this->selectedIssueNote->save();

                // Update stock ledger
                $this->selectedIssueNote->updateStockLedger();

                DB::commit();
                $this->dispatch('success', 'Issue Note fully approved and stock ledger updated!');
            }

            $this->closeModal();
            $this->render();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve: ' . $th->getMessage());
        }
    }

    public function rejectIssueNote()
    {
        if (!$this->selectedIssueNote) {
            return $this->dispatch('error', 'Issue Note not found.');
        }

        if (!$this->selectedIssueNote->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($this->selectedIssueNote->status !== 'pending') {
            return $this->dispatch('error', 'Only pending Issue Notes can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $this->selectedIssueNote->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedIssueNote->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];

            $this->selectedIssueNote->approval_history = $history;
            $this->selectedIssueNote->status = 'rejected';
            $this->selectedIssueNote->save();

            DB::commit();
            $this->dispatch('success', 'Issue Note rejected.');
            $this->closeModal();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject: ' . $th->getMessage());
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->showPreviewModal = false;
        $this->selectedIssueNote = null;
    }
}

<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\{IssueNote, Subdepartment};
use Livewire\{Component, WithPagination};

class ListIssueNotesForReceiving extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_date_from = '';
    public $filter_date_to = '';
    public $filter_receipt_status = 'pending_receipt';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $issueNotes = IssueNote::with([
                'dept_requesting',
                'dept_issuing',
                'issuer',
                'issue_note_items.item',
                'issue_note_items.batch',
            ])
            ->where('status', 'approved') // Only show approved issue notes
            ->when($selectedSubdepartmentId, function ($query) use ($selectedSubdepartmentId) {
                // Show issue notes where this subdepartment is the requesting (receiving) store
                $query->where('dept_requesting_id', $selectedSubdepartmentId);
            })
            ->when($this->filter_receipt_status !== 'all', function ($query) {
                $query->where('receipt_status', $this->filter_receipt_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('issue_number', 'like', "%{$this->search}%")
                      ->orWhere('voucher_number', 'like', "%{$this->search}%")
                      ->orWhereHas('dept_issuing', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->when($this->filter_date_from, function ($query) {
                $query->whereDate('issue_date', '>=', $this->filter_date_from);
            })
            ->when($this->filter_date_to, function ($query) {
                $query->whereDate('issue_date', '<=', $this->filter_date_to);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.storage-and-supply.issue-note.list-issue-notes-for-receiving', [
            'issueNotes' => $issueNotes,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function updatingFilterReceiptStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filter_date_from = '';
        $this->filter_date_to = '';
        $this->filter_receipt_status = 'pending_receipt';
        $this->resetPage();
    }

    public function receiveIssueNote($issueNoteId)
    {
        $this->redirect(route('receive-issue-note', ['issueNoteId' => $issueNoteId]), navigate: true);
    }
}

<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\IssueNote;
use Livewire\Component;

class ViewIssueNote extends Component
{
    public $issueNoteId;
    public $issueNote;
    public $items = [];

    public function mount($issueNoteId)
    {
        $this->issueNoteId = $issueNoteId;
        $this->loadIssueNote();
    }

    public function loadIssueNote()
    {
        $this->issueNote = IssueNote::with([
            'dept_requesting',
            'dept_issuing',
            'issuer',
            'preparer',
            'approver',
            'receiver',
            'issue_note_items.item',
            'issue_note_items.batch',
        ])->findOrFail($this->issueNoteId);

        // Load items
        foreach ($this->issueNote->issue_note_items as $item) {
            $this->items[] = [
                'item_name' => $item->item->name ?? 'N/A',
                'batch_number' => $item->batch->batch_number ?? 'N/A',
                'quantity_issued' => $item->quantity_issued,
                'buying_price' => $item->buying_price,
                'total_value' => $item->quantity_issued * $item->buying_price,
            ];
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.issue-note.view-issue-note');
    }
}

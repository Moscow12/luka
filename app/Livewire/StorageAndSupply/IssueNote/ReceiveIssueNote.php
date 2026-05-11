<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\{IssueNote, StockLedgerControl};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReceiveIssueNote extends Component
{
    public $issueNoteId;
    public $issueNote;
    public $receipt_remarks;
    public $items = [];

    protected $rules = [
        'receipt_remarks' => 'nullable|string',
        'items.*.quantity_received' => 'required|integer|min:0',
    ];

    public function mount()
    {
        $this->issueNoteId = request()->route('issueNoteId');
        $this->loadIssueNote();
    }

    public function loadIssueNote()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        if (!$selectedSubdepartmentId) {
            $this->dispatch('error', 'Please select a subdepartment first.');
            return;
        }

        $query = IssueNote::with([
            'dept_requesting',
            'dept_issuing',
            'issuer',
            'preparer',
            'issue_note_items.item',
            'issue_note_items.batch',
        ])
        ->where('status', 'approved')
        ->where('dept_requesting_id', $selectedSubdepartmentId);

        $this->issueNote = $query->findOrFail($this->issueNoteId);

        // Initialize items array
        foreach ($this->issueNote->issue_note_items as $item) {
            $this->items[$item->id] = [
                'issue_note_item_id' => $item->id,
                'item_id' => $item->item_id,
                'item_name' => $item->item->name ?? 'N/A',
                'batch_number' => $item->batch->batch_number ?? 'N/A',
                'quantity_issued' => $item->quantity_issued,
                'quantity_received' => $item->quantity_issued, // Default to full quantity
                'remarks' => '',
            ];
        }
    }

    public function confirmReceipt()
    {
        $this->validate();

        if ($this->issueNote->receipt_status === 'received') {
            $this->dispatch('error', 'This issue note has already been received.');
            return;
        }

        DB::beginTransaction();
        try {
            // Update issue note receipt status
            $this->issueNote->receipt_status = 'received';
            $this->issueNote->received_by = auth()->id();
            $this->issueNote->received_at = now();
            $this->issueNote->receipt_remarks = $this->receipt_remarks;
            $this->issueNote->save();

            // Update stock ledger for received items
            foreach ($this->items as $item) {
                if ($item['quantity_received'] > 0) {
                    // Get current balance for requesting store
                    $currentBalance = StockLedgerControl::where('item_id', $item['item_id'])
                        ->where('subdepartment_id', $this->issueNote->dept_requesting_id)
                        ->orderBy('id', 'desc')
                        ->first();

                    $preBalance = $currentBalance ? $currentBalance->post_balance : 0;
                    $postBalance = $preBalance + $item['quantity_received'];

                    // Create stock ledger entry for receipt
                    StockLedgerControl::create([
                        'item_id' => $item['item_id'],
                        'subdepartment_id' => $this->issueNote->dept_requesting_id,
                        'document_number' => $this->issueNote->id,
                        'pre_balance' => $preBalance,
                        'post_balance' => $postBalance,
                        'movement_type' => 'Issue Note Received',
                        'movement_date' => now(),
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('success', 'Issue Note received and stock ledger updated successfully!');

            $this->redirect(route('grn-issue-notes'), navigate: true);

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to confirm receipt: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.issue-note.receive-issue-note');
    }
}

<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\{IssueNote, IssueNoteItem, StoreRequisition, StockLedgerControl, GrnItemBatch};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CreateIssueNote extends Component
{
    public $requisitionId;
    public $requisition;

    // Issue Note fields
    public $issue_date;
    public $receiving_officer;
    public $issue_description;
    public $voucher_number;

    // Items with batch selection
    public $items = [];
    public $sel_item;
    public $item_search = '';

    protected $rules = [
        'issue_date' => 'required|date',
        'receiving_officer' => 'nullable|string|max:255',
        'issue_description' => 'nullable|string',
        'voucher_number' => 'nullable|string|max:255',
        'items.*.quantity_issued' => 'required|integer|min:0',
        'items.*.batch_id' => 'nullable|exists:grn_item_batches,id',
    ];

    public function mount()
    {
        $this->requisitionId = request()->route('requisitionId');
        $this->loadRequisition();
        $this->issue_date = now()->format('Y-m-d');
    }

    public function loadRequisition()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        if (!$selectedSubdepartmentId) {
            $this->dispatch('error', 'Please select a subdepartment first.');
            return;
        }

        $query = StoreRequisition::with([
            'dept_requesting',
            'dept_issuing',
            'requester',
            'requisition_items.item'
        ])->where('status', 'approved');

        $query->where('dept_issueing_id', $selectedSubdepartmentId);

        $this->requisition = $query->findOrFail($this->requisitionId);

        // Don't auto-load items - let user search and add them
        $this->sel_item = (object)[
            'id' => '',
            'name' => '',
            'quantity_issued' => 0,
            'buying_price' => 0,
        ];
    }

    public function loadItems($query)
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        if (!$selectedSubdepartmentId) {
            return collect([]);
        }

        // Get items from the approved requisition
        return DB::table('store_requisition_items as sri')
            ->join('items as i', 'i.id', '=', 'sri.item_id')
            ->where('sri.store_requisition_id', $this->requisitionId)
            ->where('i.name', 'like', "%{$query}%")
            ->select('i.id', 'i.name', 'i.code')
            ->limit(10)
            ->get()
            ->each(function($item) {
                $item->name = ($item->code ? "$item->code: " : "") . $item->name;
            });
    }

    public function loadItem($itemId)
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        if (!$selectedSubdepartmentId) {
            $this->dispatch('error', 'Please select a subdepartment first.');
            return;
        }

        // Find the requisition item
        $reqItem = DB::table('store_requisition_items as sri')
            ->join('items as i', 'i.id', '=', 'sri.item_id')
            ->where('sri.store_requisition_id', $this->requisitionId)
            ->where('sri.item_id', $itemId)
            ->select('sri.*', 'i.name', 'i.folio_number', 'i.code')
            ->first();

        if (!$reqItem) {
            $this->dispatch('error', 'Item not found in this requisition.');
            return;
        }

        // Check if item already added
        if (isset($this->items[$reqItem->id])) {
            $this->dispatch('error', 'Item already added to the list.');
            return;
        }

        $requestingBalance = $this->getStoreBalance($reqItem->item_id, $this->requisition->dept_reqesting_id);
        $issuingBalance = $this->getStoreBalance($reqItem->item_id, $this->requisition->dept_issueing_id);

        $this->sel_item = (object)[
            'requisition_item_id' => $reqItem->id,
            'item_id' => $reqItem->item_id,
            'item_name' => $reqItem->name,
            'item_folio_number' => $reqItem->folio_number ?? '',
            'product_code' => $reqItem->code ?? '',
            'quantity_required' => (int) $reqItem->quantity,
            'quantity_issued' => 0,
            'outstanding' => (int) $reqItem->quantity,
            'store_requesting_balance' => (int) $requestingBalance,
            'store_issuing_balance' => (int) $issuingBalance,
            'batch_id' => null,
            'selected_batch' => null,
            'available_batches' => $this->getAvailableBatches($reqItem->item_id, $this->requisition->dept_issueing_id),
            'total_batch_balance' => $this->getTotalBatchBalance($reqItem->item_id, $this->requisition->dept_issueing_id),
            'remarks' => '',
        ];
    }

    public function addItem()
    {
        if (empty($this->sel_item->item_id)) {
            $this->dispatch('error', 'Please select an item first.');
            return;
        }

        $this->items[$this->sel_item->requisition_item_id] = (array) $this->sel_item;

        // Reset selected item
        $this->sel_item = (object)[
            'id' => '',
            'name' => '',
            'quantity_issued' => 0,
            'buying_price' => 0,
        ];

        $this->dispatch('success', 'Item added to issue note.');
    }

    public function removeItem($itemKey)
    {
        unset($this->items[$itemKey]);
        $this->dispatch('success', 'Item removed.');
    }

    public function getStoreBalance($itemId, $subdepartmentId)
    {
        $balance = StockLedgerControl::where('item_id', $itemId)
            ->where('subdepartment_id', $subdepartmentId)
            ->orderBy('id', 'desc')
            ->first();

        return $balance ? $balance->post_balance : 0;
    }

    public function getAvailableBatches($itemId, $subdepartmentId)
    {
        // Get batches from GRN that have been approved and added to stock ledger
        return GrnItemBatch::whereHas('grn_item', function ($query) use ($itemId) {
            $query->where('item_id', $itemId);
        })
        ->whereHas('grn_item.goods_received_note', function ($query) use ($subdepartmentId) {
            $query->where('status', 'approved')
                  ->whereHas('local_purchase_order.purchase_requisition', function ($sq) use ($subdepartmentId) {
                      $sq->where('store_requesting_id', $subdepartmentId);
                  });
        })
        ->where('status', 'received')
        ->where(function($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        })
        ->orderBy('expiry_date', 'asc')
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($batch) {
            return [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : 'No Expiry',
                'quantity' => $batch->quantity_received,
                'buying_price' => $batch->buying_price,
            ];
        });
    }

    public function getTotalBatchBalance($itemId, $subdepartmentId)
    {
        $batches = $this->getAvailableBatches($itemId, $subdepartmentId);
        return $batches->sum('quantity');
    }

    public function manualSelectBatch($itemKey)
    {
        // User will select batch from dropdown
        $this->dispatch('show-batch-selector', itemKey: $itemKey);
    }

    public function autoSelectBatch($itemKey)
    {
        $item = $this->items[$itemKey];
        $batches = collect($item['available_batches']);

        if ($batches->isEmpty()) {
            $this->dispatch('error', 'No batches available for this item.');
            return;
        }

        $quantityNeeded = $item['quantity_required'];
        $quantityIssued = 0;

        // FIFO: Select batches in order of expiry date
        foreach ($batches as $batch) {
            if ($quantityIssued >= $quantityNeeded) {
                break;
            }

            $quantityFromThisBatch = min($batch['quantity'], $quantityNeeded - $quantityIssued);
            $quantityIssued += $quantityFromThisBatch;

            // Use the first batch for now (could extend to handle multiple batches per item)
            $this->items[$itemKey]['batch_id'] = $batch['id'];
            $this->items[$itemKey]['selected_batch'] = $batch;
            $this->items[$itemKey]['quantity_issued'] = $quantityIssued;
            $this->items[$itemKey]['outstanding'] = $quantityNeeded - $quantityIssued;

            if ($quantityIssued >= $quantityNeeded) {
                break;
            }
        }

        if ($quantityIssued < $quantityNeeded) {
            $this->dispatch('warning', 'Insufficient batch balance. Issued: ' . $quantityIssued . ' of ' . $quantityNeeded);
        } else {
            $this->dispatch('success', 'Batch auto-selected successfully.');
        }
    }

    public function updatedSelItem($value, $key)
    {
        // When quantity_issued changes for selected item, update outstanding
        if ($key === 'quantity_issued' && isset($this->sel_item->quantity_required)) {
            $quantityIssued = (int) $value;
            $quantityRequired = (int) $this->sel_item->quantity_required;
            $this->sel_item->outstanding = max(0, $quantityRequired - $quantityIssued);
        }
    }

    public function updatedItems($value, $key)
    {
        // When quantity_issued changes, update outstanding
        if (str_contains($key, '.quantity_issued')) {
            $itemKey = explode('.', $key)[0];
            $quantityIssued = (int) $value;
            $quantityRequired = (int) $this->items[$itemKey]['quantity_required'];
            $this->items[$itemKey]['outstanding'] = max(0, $quantityRequired - $quantityIssued);
        }
    }

    public function submit()
    {
        // Check if items have been added
        if (empty($this->items)) {
            $this->dispatch('error', 'Please add at least one item to the issue note.');
            return;
        }

        // Check if all items have batches selected
        foreach ($this->items as $item) {
            if (empty($item['batch_id']) && $item['quantity_issued'] > 0) {
                $this->dispatch('error', 'Please select a batch for all items with quantity issued.');
                return;
            }
        }

        $this->validate();

        DB::beginTransaction();
        try {
            // Create Issue Note
            $issueNote = IssueNote::create([
                'branch_id' => branch()->id,
                'store_requisition_id' => $this->requisitionId,
                'issue_number' => IssueNote::generateIssueNumber(),
                'issue_date' => $this->issue_date,
                'dept_requesting_id' => $this->requisition->dept_reqesting_id,
                'dept_issuing_id' => $this->requisition->dept_issueing_id,
                'issued_by' => auth()->id(),
                'prepared_by' => $this->requisition->requested_by,
                'receiving_officer' => $this->receiving_officer,
                'issue_description' => $this->issue_description,
                'voucher_number' => $this->voucher_number,
                'status' => 'pending',
                'current_approval_level' => 1,
                'created_by' => auth()->id(),
            ]);

            // Create Issue Note Items
            foreach ($this->items as $item) {
                if ($item['quantity_issued'] > 0) {
                    IssueNoteItem::create([
                        'issue_note_id' => $issueNote->id,
                        'store_requisition_item_id' => $item['requisition_item_id'],
                        'item_id' => $item['item_id'],
                        'batch_id' => $item['batch_id'],
                        'quantity_required' => $item['quantity_required'],
                        'quantity_issued' => $item['quantity_issued'],
                        'outstanding' => $item['outstanding'],
                        'item_folio_number' => $item['item_folio_number'],
                        'product_code' => $item['product_code'],
                        'store_requesting_balance' => $item['store_requesting_balance'],
                        'store_issuing_balance' => $item['store_issuing_balance'],
                        'total_batch_balance' => $item['total_batch_balance'],
                        'remarks' => $item['remarks'] ?? '',
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('success', 'Issue Note created successfully! Issue Number: ' . $issueNote->issue_number . ' - Waiting for Approval');

            $this->redirect(route('issue-notes-list'), navigate: true);

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to create Issue Note: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.issue-note.create-issue-note');
    }
}

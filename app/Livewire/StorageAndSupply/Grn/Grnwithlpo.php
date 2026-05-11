<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{GoodsReceivedNote, GrnItem, GrnItemBatch, LocalPurchaseOrder};
use Livewire\{Component, WithFileUploads};
use Illuminate\Support\Facades\DB;

class Grnwithlpo extends Component
{
    use WithFileUploads;

    public $lpoId;
    public $lpo;

    // GRN fields
    public $delivery_note_number;
    public $delivery_note_attachment;
    public $invoice_number;
    public $invoice_attachment;
    public $delivery_date;
    public $delivery_person;

    // Items with batches
    public $items = [];

    protected $rules = [
        'delivery_note_number' => 'required|string|max:255',
        'invoice_number' => 'required|string|max:255',
        'delivery_date' => 'required|date',
        'delivery_person' => 'nullable|string|max:255',
        'delivery_note_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'invoice_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'items.*.batches' => 'required|array|min:1',
        'items.*.batches.*.batch_number' => 'required|string',
        'items.*.batches.*.expiry_date' => 'nullable|date',
        'items.*.batches.*.buying_price' => 'required|numeric|min:0',
        'items.*.batches.*.units' => 'required|integer|min:1',
        'items.*.batches.*.items_per_unit' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->lpoId = request()->route('lpoId');
        $this->loadLPO();
        $this->delivery_date = now()->format('Y-m-d');
    }

    public function loadLPO()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $query = LocalPurchaseOrder::with([
            'purchase_requisition.store_order',
            'purchase_requisition.store_requesting',
            'supplier',
            'creator',
            'lpo_items.item'
        ]);

        // Filter by selected subdepartment if set
        if ($selectedSubdepartmentId) {
            $query->whereHas('purchase_requisition', function ($q) use ($selectedSubdepartmentId) {
                $q->where('store_requesting_id', $selectedSubdepartmentId);
            });
        }

        $this->lpo = $query->findOrFail($this->lpoId);

        // Initialize items array
        foreach ($this->lpo->lpo_items as $lpoItem) {
            $this->items[$lpoItem->id] = [
                'lpo_item_id' => $lpoItem->id,
                'item_id' => $lpoItem->item_id,
                'item_name' => $lpoItem->item->name ?? 'N/A',
                'quantity_ordered' => $lpoItem->quantity,
                'unit_price' => $lpoItem->unit_price,
                'batches' => [
                    [
                        'batch_number' => '',
                        'expiry_date' => '',
                        'buying_price' => $lpoItem->unit_price,
                        'units' => 0,
                        'items_per_unit' => 1,
                        'status' => 'received'
                    ]
                ]
            ];
        }
    }

    public function addBatch($itemKey)
    {
        $this->items[$itemKey]['batches'][] = [
            'batch_number' => '',
            'expiry_date' => '',
            'buying_price' => $this->items[$itemKey]['unit_price'],
            'units' => 0,
            'items_per_unit' => 1,
            'status' => 'received'
        ];
    }

    public function removeBatch($itemKey, $batchIndex)
    {
        if (count($this->items[$itemKey]['batches']) > 1) {
            unset($this->items[$itemKey]['batches'][$batchIndex]);
            $this->items[$itemKey]['batches'] = array_values($this->items[$itemKey]['batches']);
        }
    }

    public function getTotalReceived($itemKey)
    {
        return collect($this->items[$itemKey]['batches'] ?? [])->sum(function ($batch) {
            return ($batch['units'] ?? 0) * ($batch['items_per_unit'] ?? 0);
        });
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Upload attachments
            $deliveryNoteAttachmentPath = null;
            if ($this->delivery_note_attachment) {
                $deliveryNoteAttachmentPath = $this->delivery_note_attachment->store('grn/delivery-notes', 'public');
            }

            $invoiceAttachmentPath = null;
            if ($this->invoice_attachment) {
                $invoiceAttachmentPath = $this->invoice_attachment->store('grn/invoices', 'public');
            }

            // Create GRN
            $grn = GoodsReceivedNote::create([
                'branch_id' => branch()->id,
                'local_purchase_order_id' => $this->lpoId,
                'grn_number' => GoodsReceivedNote::generateGRNNumber(),
                'delivery_note_number' => $this->delivery_note_number,
                'delivery_note_attachment' => $deliveryNoteAttachmentPath,
                'invoice_number' => $this->invoice_number,
                'invoice_attachment' => $invoiceAttachmentPath,
                'delivery_date' => $this->delivery_date,
                'delivery_person' => $this->delivery_person,
                'status' => 'pending',
                'current_approval_level' => 1,
                'created_by' => auth()->id(),
            ]);

            // Create GRN items and batches
            foreach ($this->items as $item) {
                $grnItem = GrnItem::create([
                    'grn_id' => $grn->id,
                    'lpo_item_id' => $item['lpo_item_id'],
                    'item_id' => $item['item_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_price_ordered' => $item['unit_price'],
                    'created_by' => auth()->id(),
                ]);

                // Create batches for this item
                foreach ($item['batches'] as $batch) {
                    $units = $batch['units'] ?? 0;
                    $itemsPerUnit = $batch['items_per_unit'] ?? 0;
                    $quantityReceived = $units * $itemsPerUnit;

                    if ($quantityReceived > 0) {
                        GrnItemBatch::create([
                            'grn_item_id' => $grnItem->id,
                            'batch_number' => $batch['batch_number'],
                            'expiry_date' => $batch['expiry_date'] ?: null,
                            'buying_price' => $batch['buying_price'],
                            'units' => $units,
                            'items_per_unit' => $itemsPerUnit,
                            'amount' => $batch['buying_price'] * $quantityReceived,
                            'status' => $batch['status'] ?? 'received',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            $this->dispatch('success', 'GRN created successfully! GRN Number: ' . $grn->grn_number . ' - Waiting for Approval');

            return redirect()->route('grnlist');

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to create GRN: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.grn.grnwithlpo');
    }
}

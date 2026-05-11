<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{GrnWithoutLpo, GrnWithoutLpoItem, Supplier, Item, Subdepartment};
use Livewire\{Component, WithFileUploads};
use Illuminate\Support\Facades\DB;

class CreateGrnWithoutLpo extends Component
{
    use WithFileUploads;

    // Edit mode
    public $grnId = null;
    public $grn = null;
    public $isEditing = false;

    // GRN fields
    public $supplier_id;
    public $delivery_note_number;
    public $delivery_note_attachment;
    public $invoice_number;
    public $invoice_attachment;
    public $delivery_date;
    public $receiver;
    public $remarks;
    public $payment_type = 'cash';
    public $other_charges = 0;

    // Items
    public $items = [];
    public $availableItems = [];

    // Modal state
    public $showItemModal = false;
    public $editingIndex = null;
    public $itemSearch = '';
    public $showItemList = false;
    public $currentItem = [
        'item_id' => '',
        'units' => 0,
        'items_per_unit' => 1,
        'rejected' => 0,
        'buying_price' => 0,
        'batch_number' => '',
        'manufacture_date' => '',
        'expiry_date' => '',
        'received_date' => '',
    ];

    protected function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_note_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'invoice_number' => 'required|string|max:255',
            'invoice_attachment' => ($this->isEditing ? 'nullable' : 'required') . '|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'delivery_date' => 'required|date',
            'receiver' => 'nullable|string|max:255',
            'remarks' => 'required|string',
            'payment_type' => 'required|in:cash,credit,installment,cheque,compensation',
            'other_charges' => 'nullable|numeric|min:0',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.units' => 'required|integer|min:1',
            'items.*.items_per_unit' => 'required|integer|min:1',
            'items.*.rejected' => 'nullable|integer|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'required|string',
            'items.*.manufacture_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after:manufacture_date',
            'items.*.received_date' => 'required|date',
        ];
    }

    public function mount($grnId = null)
    {
        $this->grnId = $grnId;

        // Load GRN data if editing
        if ($this->grnId) {
            try {
                $this->grn = GrnWithoutLpo::with(['items.item', 'supplier'])
                    ->findOrFail($this->grnId);

                \Log::info('Mount: GRN found', [
                    'grn_id' => $this->grn->id,
                    'status' => $this->grn->status,
                    'items_count' => $this->grn->items->count()
                ]);

                // Check if GRN is pending
                if ($this->grn->status !== 'pending') {
                    \Log::warning('Mount: GRN not pending', ['status' => $this->grn->status]);
                    return redirect()->route('grn-without-lpo')->with('error', 'Only pending GRNs can be edited.');
                }

                $this->isEditing = true;
                $this->loadGrnData();
            } catch (\Exception $e) {
                \Log::error('Mount: Failed to load GRN', [
                    'grn_id' => $grnId,
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('grn-without-lpo')->with('error', 'Failed to load GRN: ' . $e->getMessage());
            }
        } else {
            // New GRN - set defaults
            $this->delivery_date = now()->format('Y-m-d');
            $this->currentItem['received_date'] = now()->format('Y-m-d');
        }

        $this->loadAvailableItems();
    }

    protected function loadGrnData()
    {
        try {
            $this->supplier_id = $this->grn->supplier_id;
            $this->delivery_note_number = $this->grn->delivery_note_number;
            $this->invoice_number = $this->grn->invoice_number;
            $this->delivery_date = $this->grn->delivery_date ? $this->grn->delivery_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->receiver = $this->grn->receiver ?? '';
            $this->remarks = $this->grn->remarks ?? '';
            $this->payment_type = $this->grn->payment_type ?? 'cash';
            $this->other_charges = $this->grn->other_charges ?? 0;

            // Load items
            $this->items = $this->grn->items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'units' => $item->units ?? 0,
                    'items_per_unit' => $item->items_per_unit ?? 1,
                    'rejected' => $item->rejected ?? 0,
                    'buying_price' => $item->buying_price ?? 0,
                    'batch_number' => $item->batch_number ?? '',
                    'manufacture_date' => $item->manufacture_date ? $item->manufacture_date->format('Y-m-d') : '',
                    'expiry_date' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '',
                    'received_date' => $item->received_date ? $item->received_date->format('Y-m-d') : now()->format('Y-m-d'),
                ];
            })->toArray();

            \Log::info('GRN Edit Data Loaded', [
                'grn_id' => $this->grn->id,
                'items_count' => count($this->items),
                'supplier_id' => $this->supplier_id
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load GRN data for editing', [
                'grn_id' => $this->grnId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to load GRN data: ' . $e->getMessage());
        }
    }

    public function loadAvailableItems()
    {
        // Load all active pharmacy items for Select2 dropdown
        $this->availableItems = Item::whereActive(true)
            ->where('consultation_type', 'Pharmacy')
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
    }

    public function openItemModal()
    {
        $this->resetCurrentItem();
        $this->editingIndex = null;
        $this->itemSearch = '';
        $this->showItemList = false;
        $this->showItemModal = true;
    }

    public function closeItemModal()
    {
        $this->showItemModal = false;
        $this->resetCurrentItem();
        $this->editingIndex = null;
        $this->itemSearch = '';
        $this->showItemList = false;
    }

    public function resetCurrentItem()
    {
        $this->currentItem = [
            'item_id' => '',
            'units' => 0,
            'items_per_unit' => 1,
            'rejected' => 0,
            'buying_price' => 0,
            'batch_number' => '',
            'manufacture_date' => '',
            'expiry_date' => '',
            'received_date' => now()->format('Y-m-d'),
        ];
    }

    public function updatedItemSearch()
    {
        $this->showItemList = !empty($this->itemSearch);
    }

    public function selectItem($itemId)
    {
        $this->currentItem['item_id'] = $itemId;
        $selectedItem = $this->availableItems->firstWhere('id', $itemId);
        if ($selectedItem) {
            $this->itemSearch = ($selectedItem->code ? $selectedItem->code . ' - ' : '') . $selectedItem->name;
        }
        $this->showItemList = false;
    }

    public function getFilteredItemsProperty()
    {
        if (empty($this->itemSearch)) {
            return collect();
        }

        return $this->availableItems->filter(function ($item) {
            $search = strtolower($this->itemSearch);
            return str_contains(strtolower($item->name), $search) ||
                   str_contains(strtolower($item->code ?? ''), $search);
        })->take(20);
    }

    public function editItem($index)
    {
        $this->editingIndex = $index;
        $this->currentItem = $this->items[$index];

        // Pre-populate search with selected item
        if (!empty($this->currentItem['item_id'])) {
            $selectedItem = $this->availableItems->firstWhere('id', $this->currentItem['item_id']);
            if ($selectedItem) {
                $this->itemSearch = ($selectedItem->code ? $selectedItem->code . ' - ' : '') . $selectedItem->name;
            }
        }

        $this->showItemModal = true;
    }

    public function saveItem()
    {
        $this->validate([
            'currentItem.item_id' => 'required|exists:items,id',
            'currentItem.units' => 'required|integer|min:1',
            'currentItem.items_per_unit' => 'required|integer|min:1',
            'currentItem.rejected' => 'nullable|integer|min:0',
            'currentItem.buying_price' => 'required|numeric|min:0',
            'currentItem.batch_number' => 'required|string',
            'currentItem.manufacture_date' => 'nullable|date',
            'currentItem.expiry_date' => 'nullable|date|after:currentItem.manufacture_date',
            'currentItem.received_date' => 'required|date',
        ]);

        if ($this->editingIndex !== null) {
            // Update existing item
            $this->items[$this->editingIndex] = $this->currentItem;
        } else {
            // Add new item
            $this->items[] = $this->currentItem;
        }

        $this->closeItemModal();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getTotalQuantity($index)
    {
        $units = (int) ($this->items[$index]['units'] ?? 0);
        $itemsPerUnit = (int) ($this->items[$index]['items_per_unit'] ?? 0);
        $rejected = (int) ($this->items[$index]['rejected'] ?? 0);
        return ($units * $itemsPerUnit) - $rejected;
    }

    public function getTotalValue()
    {
        $total = 0;
        foreach ($this->items as $index => $item) {
            $quantity = $this->getTotalQuantity($index);
            $total += $quantity * (float) ($item['buying_price'] ?? 0);
        }
        return $total + (float) ($this->other_charges ?? 0);
    }

    public function submit()
    {
        \Log::info('GRN Submit Started', ['isEditing' => $this->isEditing]);

        // Validate items exist
        if (empty($this->items)) {
            \Log::warning('GRN Submit: No items');
            $this->dispatch('error', 'Please add at least one item to the GRN.');
            return;
        }

        \Log::info('GRN Submit: Items validated', ['item_count' => count($this->items)]);

        try {
            $this->validate();
            \Log::info('GRN Submit: Validation passed');
        } catch (\Exception $e) {
            \Log::error('GRN Submit: Validation failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');
        if (!$selectedSubdepartmentId) {
            \Log::warning('GRN Submit: No subdepartment selected');
            $this->dispatch('error', 'Please select a subdepartment first.');
            return;
        }

        \Log::info('GRN Submit: Subdepartment validated', ['subdepartment_id' => $selectedSubdepartmentId]);

        DB::beginTransaction();
        try {
            \Log::info('GRN Submit: Transaction started');

            if ($this->isEditing) {
                // Update existing GRN
                $grn = $this->grn;

                // Upload attachments if new files provided
                $deliveryNoteAttachmentPath = $grn->delivery_note_attachment;
                if ($this->delivery_note_attachment) {
                    $deliveryNoteAttachmentPath = $this->delivery_note_attachment->store('grn-without-lpo/delivery-notes', 'public');
                    \Log::info('GRN Submit: Delivery note uploaded', ['path' => $deliveryNoteAttachmentPath]);
                }

                $invoiceAttachmentPath = $grn->invoice_attachment;
                if ($this->invoice_attachment) {
                    $invoiceAttachmentPath = $this->invoice_attachment->store('grn-without-lpo/invoices', 'public');
                    \Log::info('GRN Submit: Invoice uploaded', ['path' => $invoiceAttachmentPath]);
                }

                // Update GRN
                \Log::info('GRN Submit: Updating GRN record');
                $grn->update([
                    'supplier_id' => $this->supplier_id,
                    'delivery_note_number' => $this->delivery_note_number,
                    'delivery_note_attachment' => $deliveryNoteAttachmentPath,
                    'invoice_number' => $this->invoice_number,
                    'invoice_attachment' => $invoiceAttachmentPath,
                    'delivery_date' => $this->delivery_date,
                    'receiver' => $this->receiver,
                    'remarks' => $this->remarks,
                    'payment_type' => $this->payment_type,
                    'other_charges' => $this->other_charges ?? 0,
                ]);

                // Delete existing items and recreate
                \Log::info('GRN Submit: Deleting old items');
                $grn->items()->delete();

                // Create new items
                \Log::info('GRN Submit: Creating new items');
                foreach ($this->items as $index => $item) {
                    if (!empty($item['item_id']) && $item['units'] > 0) {
                        GrnWithoutLpoItem::create([
                            'grn_without_lpo_id' => $grn->id,
                            'item_id' => $item['item_id'],
                            'units' => $item['units'],
                            'items_per_unit' => $item['items_per_unit'],
                            'rejected' => $item['rejected'] ?? 0,
                            'buying_price' => $item['buying_price'],
                            'batch_number' => $item['batch_number'],
                            'manufacture_date' => $item['manufacture_date'] ?: null,
                            'expiry_date' => $item['expiry_date'] ?: null,
                            'received_date' => $item['received_date'],
                            'created_by' => auth()->id(),
                        ]);
                        \Log::info('GRN Submit: Item created', ['index' => $index, 'item_id' => $item['item_id']]);
                    }
                }

                DB::commit();
                \Log::info('GRN Submit: Transaction committed');

                session()->flash('success', 'GRN Without LPO updated successfully! GRN Number: ' . $grn->grn_number);
                \Log::info('GRN Submit: Success message set in session');

            } else {
                // Create new GRN
                // Upload attachments
                $deliveryNoteAttachmentPath = null;
                if ($this->delivery_note_attachment) {
                    $deliveryNoteAttachmentPath = $this->delivery_note_attachment->store('grn-without-lpo/delivery-notes', 'public');
                    \Log::info('GRN Submit: Delivery note uploaded', ['path' => $deliveryNoteAttachmentPath]);
                }

                $invoiceAttachmentPath = null;
                if ($this->invoice_attachment) {
                    $invoiceAttachmentPath = $this->invoice_attachment->store('grn-without-lpo/invoices', 'public');
                    \Log::info('GRN Submit: Invoice uploaded', ['path' => $invoiceAttachmentPath]);
                }

                // Create GRN
                \Log::info('GRN Submit: Creating GRN record');
                $grn = GrnWithoutLpo::create([
                    'branch_id' => branch()->id,
                    'supplier_id' => $this->supplier_id,
                    'subdepartment_id' => $selectedSubdepartmentId,
                    'grn_number' => GrnWithoutLpo::generateGRNNumber(),
                    'delivery_note_number' => $this->delivery_note_number,
                    'delivery_note_attachment' => $deliveryNoteAttachmentPath,
                    'invoice_number' => $this->invoice_number,
                    'invoice_attachment' => $invoiceAttachmentPath,
                    'delivery_date' => $this->delivery_date,
                    'receiver' => $this->receiver,
                    'remarks' => $this->remarks,
                    'payment_type' => $this->payment_type,
                    'other_charges' => $this->other_charges ?? 0,
                    'status' => 'pending',
                    'current_approval_level' => 1,
                    'created_by' => auth()->id(),
                ]);
                \Log::info('GRN Submit: GRN created', ['grn_id' => $grn->id, 'grn_number' => $grn->grn_number]);

                // Create GRN items
                \Log::info('GRN Submit: Creating GRN items');
                foreach ($this->items as $index => $item) {
                    if (!empty($item['item_id']) && $item['units'] > 0) {
                        GrnWithoutLpoItem::create([
                            'grn_without_lpo_id' => $grn->id,
                            'item_id' => $item['item_id'],
                            'units' => $item['units'],
                            'items_per_unit' => $item['items_per_unit'],
                            'rejected' => $item['rejected'] ?? 0,
                            'buying_price' => $item['buying_price'],
                            'batch_number' => $item['batch_number'],
                            'manufacture_date' => $item['manufacture_date'] ?: null,
                            'expiry_date' => $item['expiry_date'] ?: null,
                            'received_date' => $item['received_date'],
                            'created_by' => auth()->id(),
                        ]);
                        \Log::info('GRN Submit: Item created', ['index' => $index, 'item_id' => $item['item_id']]);
                    }
                }

                DB::commit();
                \Log::info('GRN Submit: Transaction committed');

                session()->flash('success', 'GRN Without LPO created successfully! GRN Number: ' . $grn->grn_number . ' - Waiting for Approval');
                \Log::info('GRN Submit: Success message set in session');
            }

            \Log::info('GRN Submit: Attempting redirect to grn-without-lpo route');
            return redirect()->route('grn-without-lpo');

        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('GRN Submit: Failed', [
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            $this->dispatch('error', 'Failed to ' . ($this->isEditing ? 'update' : 'create') . ' GRN: ' . $th->getMessage());
        }
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.storage-and-supply.grn.create-grn-without-lpo', [
            'suppliers' => $suppliers,
        ]);
    }
}

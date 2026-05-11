<?php

namespace App\Livewire\StorageAndSupply\Requisitions;

use App\Models\{Item, StoreRequisition, StoreRequisitionItem, Subdepartment};
use Illuminate\Support\{Arr, Facades\DB};
use Livewire\Component;
use stdClass;

class StockRequisitions extends Component
{
    public $requisition, $sel_item;
    public $stores_requesting = [], $stores_issuing = [];
    public $selected_items = [];
    public $cons_type = '%%', $item_name = '';
    public $balance_requesting = 0, $balance_issuing = 0;
    public $editingItemId = null;

    protected $rules = [
        'requisition.requisition_date' => 'required|date|before_or_equal:today',
        'requisition.dept_reqesting_id' => 'required|exists:subdepartments,id',
        'requisition.dept_issueing_id' => 'required|exists:subdepartments,id|different:requisition.dept_reqesting_id',
        'requisition.priority' => 'required|in:normal,urgent,emergency',
        'requisition.remarks' => 'nullable|string|max:500',
        'sel_item.id' => 'required|exists:items,id',
        'sel_item.quantity' => 'required|numeric|min:1',
        'sel_item.remarks' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'requisition.dept_issueing_id.different' => 'Issuing store must be different from requesting store.',
    ];

    public function mount()
    {
        $this->requisition = new StoreRequisition([
            'requisition_date' => date('Y-m-d'),
            'requested_by' => auth()->id(),
            'priority' => 'normal',
            'status' => 'pending',
        ]);
        $this->sel_item = new Item();
        $this->loadStores();
    }

    public function hydrate()
    {
        // Ensure requested_by is always set after Livewire hydration
        if (isset($this->requisition) && !$this->requisition->id && !$this->requisition->requested_by) {
            $this->requisition->requested_by = auth()->id();
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.requisitions.stock-requisitions');
    }

    public function loadStores()
    {
        // Stores requesting: Storage and Supply OR Pharmacy
        $this->stores_requesting = Subdepartment::whereIn('d.nature_of_department', ['Storage And Supply', 'Pharmacy'])
            ->join('departments as d', 'd.id', 'subdepartments.department_id')
            ->select('subdepartments.*', 'd.nature_of_department')
            ->get();

        // Stores issuing: Only Storage and Supply
        $this->stores_issuing = Subdepartment::where('d.nature_of_department', 'Storage And Supply')
            ->join('departments as d', 'd.id', 'subdepartments.department_id')
            ->select('subdepartments.*')
            ->get();
    }

    public function storeChanged()
    {
        // Check if there's a pending requisition for this user
        if ($req = StoreRequisition::whereStatus('pending')
            ->where('requested_by', auth()->id())
            ->whereNull('approved_by')
            ->first()
        ) {
            $this->requisition = $req;
        } else {
            // Preserve requested_by when stores change
            if (!$this->requisition->requested_by) {
                $this->requisition->requested_by = auth()->id();
            }
        }
        $this->updateBalances();
    }

    public function saveRequisition()
    {
        $this->validate($this->toValidate('requisition'));

        // Ensure requested_by is set
        if (!$this->requisition->requested_by) {
            $this->requisition->requested_by = auth()->id();
        }

        if (!$this->requisition->save()) {
            return $this->dispatch('error', 'Error: Failed to save requisition. Please try again.');
        }

        $this->dispatch('success', 'Requisition saved successfully.');
    }

    public function loadItem($id)
    {
        if (!$this->requisition->dept_reqesting_id || !$this->requisition->dept_issueing_id) {
            return $this->dispatch('error', 'Please select both requesting and issuing stores first.');
        }

        if (!$item = Item::find($id)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        $this->sel_item = $item;
        $this->sel_item->quantity = 1;
        $this->sel_item->remarks = '';
        $this->updateBalances();
    }

    public function updateBalances()
    {
        if ($this->sel_item->id && $this->requisition->dept_reqesting_id && $this->requisition->dept_issueing_id) {
            $this->balance_requesting = itemBalance($this->requisition->dept_reqesting_id, $this->sel_item->id);
            $this->balance_issuing = itemBalance($this->requisition->dept_issueing_id, $this->sel_item->id);
        } else {
            $this->balance_requesting = 0;
            $this->balance_issuing = 0;
        }
    }

    public function addItem()
    {
        $this->validate();

        if (!isset($this->sel_item->id)) {
            return $this->dispatch('error', 'Please select an item first.');
        }

        // Warning if insufficient stock but allow to proceed
        $lowStockWarning = false;
        if ($this->balance_issuing < $this->sel_item->quantity) {
            $lowStockWarning = true;
        }

        DB::beginTransaction();
        try {
            // Save requisition if not already saved
            if (!isset($this->requisition->id)) {
                $this->saveRequisition();
            }

            // Add item to requisition
            $this->requisition->requisition_items()->create([
                'item_id' => $this->sel_item->id,
                'quantity' => $this->sel_item->quantity,
                'remarks' => $this->sel_item->remarks,
                'requested_by' => auth()->id(),
            ]);

            DB::commit();
            $this->resetItemForm();
            $this->dispatch('refresh');

            if ($lowStockWarning) {
                $this->dispatch('success', 'Item added successfully! Note: Issuing store has low/zero stock.');
            } else {
                $this->dispatch('success', 'Item added successfully!');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to add item: ' . $th->getMessage());
        }
    }

    public function editItem($id)
    {
        if (!$item = StoreRequisitionItem::with('item')->find($id)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        $this->editingItemId = $id;
        $this->sel_item = $item->item;
        $this->sel_item->quantity = $item->quantity;
        $this->sel_item->remarks = $item->remarks;
        $this->updateBalances();
    }

    public function updateItem()
    {
        if (!$this->editingItemId) {
            return $this->dispatch('error', 'No item selected for editing.');
        }

        $this->validate();

        if (!$item = StoreRequisitionItem::find($this->editingItemId)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        // Warning if insufficient stock but allow to proceed
        $lowStockWarning = false;
        if ($this->balance_issuing < $this->sel_item->quantity) {
            $lowStockWarning = true;
        }

        DB::beginTransaction();
        try {
            $item->update([
                'item_id' => $this->sel_item->id,
                'quantity' => $this->sel_item->quantity,
                'remarks' => $this->sel_item->remarks,
            ]);

            DB::commit();
            $this->resetItemForm();
            $this->dispatch('refresh');

            if ($lowStockWarning) {
                $this->dispatch('success', 'Item updated successfully! Note: Issuing store has low/zero stock.');
            } else {
                $this->dispatch('success', 'Item updated successfully!');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to update item: ' . $th->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->resetItemForm();
        $this->dispatch('refresh');
    }

    protected function resetItemForm()
    {
        $this->editingItemId = null;
        $this->sel_item = new Item();
        $this->balance_requesting = 0;
        $this->balance_issuing = 0;
    }

    public function loadSelectedItems()
    {
        $items = [];
        if (!isset($this->requisition->id)) {
            return $items;
        }

        foreach ($this->requisition->requisition_items as $itm) {
            $sel = new stdClass;
            $sel->id = $itm->id;
            $sel->item_id = $itm->item_id;
            $sel->name = $itm->item->name ?? 'Unknown';
            $sel->code = $itm->item->code ?? '';
            $sel->unit = $itm->item->unit ?? '';
            $sel->quantity = $itm->quantity;
            $sel->remarks = $itm->remarks;
            $sel->balance_requesting = itemBalance($this->requisition->dept_reqesting_id, $itm->item_id);
            $sel->balance_issuing = itemBalance($this->requisition->dept_issueing_id, $itm->item_id);
            $items[] = $sel;
        }
        return $items;
    }

    public function deleteItem($id)
    {
        if (!$item = StoreRequisitionItem::find($id)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        $deleteRequisition = $this->requisition->requisition_items->count() <= 1;

        DB::beginTransaction();
        try {
            $item->delete();
            if ($deleteRequisition) {
                $this->requisition->delete();
                $this->requisition = new StoreRequisition([
                    'requisition_date' => date('Y-m-d'),
                    'requested_by' => auth()->id(),
                    'priority' => 'normal',
                    'status' => 'pending',
                ]);
            }
            DB::commit();
            $this->dispatch('refresh');
            $this->dispatch('success', 'Item removed successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to remove item. Please try again.');
        }
    }

    public function submitRequisition()
    {
        if (!isset($this->requisition->id) || !$this->requisition->requisition_items->count()) {
            return $this->dispatch('error', 'Please add at least one item before submitting.');
        }

        DB::beginTransaction();
        try {
            $this->requisition->status = 'pending';
            $this->requisition->save();

            DB::commit();
            $this->dispatch('success', 'Requisition submitted successfully!');

            // Reset form
            $this->requisition = new StoreRequisition([
                'requisition_date' => date('Y-m-d'),
                'requested_by' => auth()->id(),
                'priority' => 'normal',
                'status' => 'pending',
            ]);
            $this->sel_item = new Item();
            $this->dispatch('refresh');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to submit requisition. Please try again.');
        }
    }

    public function loadItems($q)
    {
        return Item::whereRestockable(true)
            ->where('consultation_type', 'like', $this->cons_type)
            ->where('name', 'like', "%$q%")
            ->get(['id', 'code', 'name', 'unit'])
            ->each(fn($itm) => $itm->name = "$itm->code: $itm->name");
    }

    protected function toValidate($form = 'requisition')
    {
        return Arr::where($this->rules, function ($value, $key) use ($form) {
            return str($key)->startsWith("$form.");
        });
    }
}

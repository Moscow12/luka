<?php

namespace App\Livewire\StorageAndSupply\StoreOrder;

use App\Models\{Item, StoreOrder, StoreOrderItem, Subdepartment};
use Illuminate\Support\{Arr, Facades\DB};
use Livewire\Component;
use stdClass;

class Neworder extends Component
{
    public $order, $sel_item;
    public $stores = [];
    public $cons_type = '%%';
    public $current_balance = 0;

    protected $rules = [
        'order.order_date' => 'required|date|before_or_equal:today',
        'order.dept_ordering_id' => 'required|exists:subdepartments,id',
        'order.priority' => 'required|in:normal,urgent,emergency',
        'order.remarks' => 'nullable|string|max:500',
        'sel_item.id' => 'required|exists:items,id',
        'sel_item.units' => 'required|numeric|min:1',
        'sel_item.per_unit' => 'required|numeric|min:1',
        'sel_item.quantity' => 'required|numeric|min:1',
        'sel_item.remarks' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->order = new StoreOrder([
            'order_date' => date('Y-m-d'),
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
        if (isset($this->order) && !$this->order->id && !$this->order->requested_by) {
            $this->order->requested_by = auth()->id();
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.store-order.neworder');
    }

    public function loadStores()
    {
        // Stores: Storage and Supply only
        $this->stores = Subdepartment::where('d.nature_of_department', 'Storage And Supply')
            ->join('departments as d', 'd.id', 'subdepartments.department_id')
            ->select('subdepartments.*')
            ->get();
    }

    public function storeChanged()
    {
        // Check if there's a pending order for this user
        if ($ord = StoreOrder::whereStatus('pending')
            ->where('requested_by', auth()->id())
            ->whereNull('approved_by')
            ->first()
        ) {
            $this->order = $ord;
        } else {
            // Preserve requested_by when store changes
            if (!$this->order->requested_by) {
                $this->order->requested_by = auth()->id();
            }
        }
        $this->updateBalance();
    }

    public function saveOrder()
    {
        $this->validate($this->toValidate('order'));

        // Ensure requested_by is set
        if (!$this->order->requested_by) {
            $this->order->requested_by = auth()->id();
        }

        if (!$this->order->save()) {
            return $this->dispatch('error', 'Error: Failed to save order. Please try again.');
        }

        $this->dispatch('success', 'Order saved successfully.');
    }

    public function loadItem($id)
    {
        if (!$this->order->dept_ordering_id) {
            return $this->dispatch('error', 'Please select ordering store first.');
        }

        if (!$item = Item::find($id)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        $this->sel_item = $item;
        $this->sel_item->units = 1;
        $this->sel_item->per_unit = 1;
        $this->sel_item->quantity = 1;
        $this->sel_item->remarks = '';
        $this->updateBalance();
    }

    public function setQuantity()
    {
        $item = $this->sel_item;
        if (!$item->units || !is_numeric($item->units)) {
            $this->sel_item->units = 1;
        }
        if (!$item->per_unit || !is_numeric($item->per_unit)) {
            $this->sel_item->per_unit = 1;
        }
        $this->sel_item->quantity = $this->sel_item->per_unit * $this->sel_item->units;
        $this->updateBalance();
    }

    public function updateBalance()
    {
        if ($this->sel_item->id && $this->order->dept_ordering_id) {
            $this->current_balance = itemBalance($this->order->dept_ordering_id, $this->sel_item->id);
        } else {
            $this->current_balance = 0;
        }
    }

    public function addItem()
    {
        $this->validate();

        if (!isset($this->sel_item->id)) {
            return $this->dispatch('error', 'Please select an item first.');
        }

        DB::beginTransaction();
        try {
            // Save order if not already saved
            if (!isset($this->order->id)) {
                $this->saveOrder();
            }

            // Add item to order
            $this->order->order_items()->create([
                'item_id' => $this->sel_item->id,
                'units' => $this->sel_item->units,
                'itemperunit' => $this->sel_item->per_unit,
                'remarks' => $this->sel_item->remarks,
            ]);

            DB::commit();
            $this->sel_item = new Item();
            $this->current_balance = 0;
            $this->dispatch('refresh');
            $this->dispatch('success', 'Item added successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to add item: ' . $th->getMessage());
        }
    }

    public function loadSelectedItems()
    {
        $items = [];
        if (!isset($this->order->id)) {
            return $items;
        }

        foreach ($this->order->order_items as $itm) {
            $sel = new stdClass;
            $sel->id = $itm->id;
            $sel->item_id = $itm->item_id;
            $sel->name = $itm->item->name ?? 'Unknown';
            $sel->code = $itm->item->code ?? '';
            $sel->unit = $itm->item->unit ?? '';
            $sel->units = $itm->units;
            $sel->per_unit = $itm->itemperunit;
            $sel->quantity = $itm->units * $itm->itemperunit;
            $sel->remarks = $itm->remarks;
            $sel->current_balance = itemBalance($this->order->dept_ordering_id, $itm->item_id);
            $items[] = $sel;
        }
        return $items;
    }

    public function deleteItem($id)
    {
        if (!$item = StoreOrderItem::find($id)) {
            return $this->dispatch('error', 'Item not found. Please try again.');
        }

        $deleteOrder = $this->order->order_items->count() <= 1;

        DB::beginTransaction();
        try {
            $item->delete();
            if ($deleteOrder) {
                $this->order->delete();
                $this->order = new StoreOrder([
                    'order_date' => date('Y-m-d'),
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

    public function submitOrder()
    {
        if (!isset($this->order->id) || !$this->order->order_items->count()) {
            return $this->dispatch('error', 'Please add at least one item before submitting.');
        }

        DB::beginTransaction();
        try {
            $this->order->status = 'pending';
            $this->order->save();

            DB::commit();
            $this->dispatch('success', 'Order submitted successfully!');

            // Reset form
            $this->order = new StoreOrder([
                'order_date' => date('Y-m-d'),
                'requested_by' => auth()->id(),
                'priority' => 'normal',
                'status' => 'pending',
            ]);
            $this->sel_item = new Item();
            $this->dispatch('refresh');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to submit order. Please try again.');
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

    protected function toValidate($form = 'order')
    {
        return Arr::where($this->rules, function ($value, $key) use ($form) {
            return str($key)->startsWith("$form.");
        });
    }
}

<?php

namespace App\Livewire\StorageAndSupply;

use App\Models\chopitems as ChopItem;
use App\Models\DepartmentStore;
use App\Models\PhysicalCount as ModelsPhysicalCount;
use App\Models\PhysicalCountItem;
use App\Models\StockLedgerControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PhysicalCount extends Component
{
    public $counts;

    public $departmentStores;

    public $search = '';

    public $department_store_id;

    public $count_date;

    public $remarks;

    public $lines = [];

    public $count_id;

    public $modalMode = 'create'; // or 'view'

    public $showModal = false;

    public function mount()
    {
        $this->count_date = now()->toDateString();
        $this->departmentStores = DepartmentStore::where('status', 'active')->orderBy('name')->get();
        $this->listdata();
    }

    public function listdata()
    {
        $this->counts = ModelsPhysicalCount::with(['departmentStore', 'addedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('departmentStore', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('count_date')
            ->get();
    }

    public function updatedSearch()
    {
        $this->listdata();
    }

    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->count_date = now()->toDateString();
        $this->department_store_id = null;
        $this->remarks = null;
        $this->lines = [];
        $this->count_id = null;
        $this->showModal = true;
    }

    public function loadStockableItems()
    {
        $this->validate([
            'department_store_id' => ['required', 'exists:department_stores,id'],
        ]);

        $items = ChopItem::where('is_active', true)
            ->where('can_be_stocked', true)
            ->orderBy('name')
            ->get();

        $this->lines = $items->map(function ($item) {
            $systemQty = $this->currentBalance($item->id, $this->department_store_id);

            return [
                'item_id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'system_qty' => $systemQty,
                'counted_qty' => null,
                'remarks' => null,
            ];
        })->toArray();
    }

    protected function currentBalance($itemId, $departmentStoreId)
    {
        $last = StockLedgerControl::where('item_id', $itemId)
            ->where('department_store_id', $departmentStoreId)
            ->orderByDesc('movement_date')
            ->orderByDesc('created_at')
            ->first();

        return $last ? (int) $last->post_balance : 0;
    }

    public function save()
    {
        $this->validate([
            'department_store_id' => ['required', 'exists:department_stores,id'],
            'count_date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () {
            $count = ModelsPhysicalCount::create([
                'department_store_id' => $this->department_store_id,
                'count_date' => $this->count_date,
                'status' => 'draft',
                'remarks' => $this->remarks,
                'added_by' => Auth::id(),
            ]);

            foreach ($this->lines as $line) {
                PhysicalCountItem::create([
                    'physical_count_id' => $count->id,
                    'item_id' => $line['item_id'],
                    'system_qty' => $line['system_qty'],
                    'counted_qty' => $line['counted_qty'] === '' ? null : $line['counted_qty'],
                    'remarks' => $line['remarks'] ?: null,
                ]);
            }

            $this->count_id = $count->id;
        });

        $this->showModal = false;
        $this->listdata();
        session()->flash('success', 'Physical count recorded successfully!');
    }

    public function view($id)
    {
        $count = ModelsPhysicalCount::with(['departmentStore', 'items.item', 'addedBy', 'approvedBy'])->findOrFail($id);
        $this->count_id = $count->id;
        $this->department_store_id = $count->department_store_id;
        $this->count_date = $count->count_date->toDateString();
        $this->remarks = $count->remarks;
        $this->modalMode = 'view';
        $this->lines = $count->items->map(function ($line) {
            return [
                'item_id' => $line->item_id,
                'name' => $line->item->name,
                'unit' => $line->item->unit,
                'system_qty' => $line->system_qty,
                'counted_qty' => $line->counted_qty,
                'remarks' => $line->remarks,
            ];
        })->toArray();
        $this->showModal = true;
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $count = ModelsPhysicalCount::with('items')->findOrFail($id);

            if ($count->status === 'approved') {
                return;
            }

            foreach ($count->items as $line) {
                if (is_null($line->counted_qty) || $line->counted_qty === $line->system_qty) {
                    continue;
                }

                StockLedgerControl::create([
                    'item_id' => $line->item_id,
                    'department_store_id' => $count->department_store_id,
                    'document_number' => 0,
                    'pre_balance' => $line->system_qty,
                    'post_balance' => $line->counted_qty,
                    'movement_type' => 'ADJUSTMENT',
                    'movement_date' => $count->count_date,
                    'added_by' => Auth::id(),
                ]);
            }

            $count->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        $this->listdata();
        session()->flash('success', 'Physical count approved and stock adjusted!');
    }

    public function delete($id)
    {
        $count = ModelsPhysicalCount::findOrFail($id);

        if ($count->status === 'approved') {
            session()->flash('error', 'Cannot delete an approved physical count.');

            return;
        }

        $count->delete();
        $this->listdata();
        session()->flash('success', 'Physical count deleted successfully!');
    }

    public function render()
    {
        return view('livewire.storage-and-supply.physical-count');
    }
}

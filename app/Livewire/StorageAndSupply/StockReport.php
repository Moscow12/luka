<?php

namespace App\Livewire\StorageAndSupply;

use App\Models\{Classification, Item, StockLedgerControl as Sl};
use Livewire\Component;

class StockReport extends Component
{
    public $items, $stores, $classifications, $classification = '%', $stock_items = [];
    public $store_id, $from, $to, $item_name = '', $item, $balance_forward;

    public function render()
    {
        return view('livewire.storage-and-supply.reports.stock-report');
    }

    public function mount()
    {
        $this->classifications = Classification::whereActive(true)->get();
        $this->stores = stores();
        $this->loadItems();
        $this->dispatch('modal-show', 'stock-report');
    }

    public function loadItems($query = '')
    {
        if ($query !== '') {
            $this->item_name = $query;
        }

        $this->items = Item::whereRestockable(true)
            ->where('name', 'like', '%' . $this->item_name . '%')
            ->where('classification_id',  'like', $this->classification)
            ->get(['id', 'code', 'name'])
            ->map(function($itm) {
                return [
                    'id' => $itm->id,
                    'name' => $itm->code . ': ' . $itm->name
                ];
            });

        return $this->items;
    }

    public function loadItem($id)
    {
        if (!$this->item = Item::find($id)) {
            return $this->dispatch('error', 'Item not found. Refresh and try again.');
        }
        $this->loadItems();
    }

    public function loadStockItems()
    {
        $this->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'store_id' => 'required|numeric|min:1',
        ]);

        if (!isset($this->item->id)) {
            return $this->dispatch('error', 'Error, Item not selected.');
        }

        $this->stock_items = Sl::where('stock_ledger_controls.subdepartment_id', $this->store_id)
            ->where('item_id', $this->item->id)
            ->whereBetween('movement_date', [$this->from, $this->to])
            ->join('items', 'items.id', 'stock_ledger_controls.item_id')
            // ->join('grn_open_balances as grn', 'grn.id', 'stock_ledger_controls.document_number')
            ->with(['store', 'user', 'patient', 'payment.visit.patient'])
            ->select('stock_ledger_controls.*', 'movement_date as date', 'document_number', 'movement_type', 'items.name')
            ->get();
        $this->balance_forward = Sl::where('stock_ledger_controls.subdepartment_id', $this->store_id)
            ->where('item_id', $this->item->id)->where('movement_date', '<', $this->from)
            ->sum('qty');
    }

    public function resetFields()
    {
        // $this->dispatch('open', url()->previous());
    }
}

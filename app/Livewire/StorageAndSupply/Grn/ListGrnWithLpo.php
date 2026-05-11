<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{LocalPurchaseOrder, Subdepartment, Supplier};
use Livewire\{Component, WithPagination};

class ListGrnWithLpo extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_date_from = '';
    public $filter_date_to = '';
    public $filter_store = '';
    public $filter_supplier = '';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $lpos = LocalPurchaseOrder::with([
                'purchase_requisition.store_order',
                'purchase_requisition.store_requesting',
                'supplier',
                'creator',
                'lpo_items',
                'goods_received_notes' => function($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->withCount('lpo_items')
            ->where('status', 'approved')
            ->whereDoesntHave('goods_received_notes', function ($query) {
                $query->where('status', 'approved');
            })
            ->when($selectedSubdepartmentId, function ($query) use ($selectedSubdepartmentId) {
                $query->whereHas('purchase_requisition', function ($sq) use ($selectedSubdepartmentId) {
                    $sq->where('store_requesting_id', $selectedSubdepartmentId);
                });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('lpo_number', 'like', "%{$this->search}%")
                      ->orWhereHas('purchase_requisition', fn($sq) =>
                          $sq->where('pr_number', 'like', "%{$this->search}%")
                      )
                      ->orWhereHas('purchase_requisition.store_order', fn($sq) =>
                          $sq->where('order_number', 'like', "%{$this->search}%")
                      )
                      ->orWhereHas('supplier', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->when($this->filter_date_from, function ($query) {
                $query->whereDate('created_at', '>=', $this->filter_date_from);
            })
            ->when($this->filter_date_to, function ($query) {
                $query->whereDate('created_at', '<=', $this->filter_date_to);
            })
            ->when($this->filter_store, function ($query) {
                $query->whereHas('purchase_requisition', function ($sq) {
                    $sq->where('store_requesting_id', $this->filter_store);
                });
            })
            ->when($this->filter_supplier, function ($query) {
                $query->where('supplier_id', $this->filter_supplier);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stores = Subdepartment::whereIn('d.nature_of_department', ['Pharmacy', 'Storage And Supply'])
            ->join('departments as d', 'd.id', 'subdepartments.department_id')
            ->select('subdepartments.*')
            ->orderBy('subdepartments.name')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.storage-and-supply.grn.list-grn-with-lpo', [
            'lpos' => $lpos,
            'stores' => $stores,
            'suppliers' => $suppliers,
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

    public function updatingFilterStore()
    {
        $this->resetPage();
    }

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filter_date_from = '';
        $this->filter_date_to = '';
        $this->filter_store = '';
        $this->filter_supplier = '';
        $this->resetPage();
    }

    public function createGRN($lpoId)
    {
        return redirect()->route('grnwithlpo', ['lpoId' => $lpoId]);
    }
}

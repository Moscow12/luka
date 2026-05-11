<?php

namespace App\Livewire\Procurement;

use App\Models\{StoreOrder, StoreOrderItem, PurchaseRequisition, PurchaseRequisitionItem, Supplier};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;
use stdClass;

class Orders extends Component
{
    use WithPagination;

    public $selectedOrder = null;
    public $showItemsModal = false;
    public $search = '';
    public $filter_status = 'approved';
    public $itemSuppliers = []; // Array to store supplier_id for each item
    public $suppliers = [];
    public $groupedItems = []; // Items grouped by supplier for preview

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $orders = StoreOrder::with(['dept_ordering', 'requester', 'approver'])
            ->when($this->filter_status !== 'all', function($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('dept_ordering', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('requester', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhere('id', 'like', "%{$this->search}%")
                      ->orWhere('order_description', 'like', "%{$this->search}%")
                      ->orWhere('supplier', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.procurement.orders', [
            'orders' => $orders,
        ]);
    }

    public function viewOrderItems($id)
    {
        $this->selectedOrder = StoreOrder::with([
            'order_items.item',
            'dept_ordering',
            'requester',
            'approver'
        ])->find($id);

        if ($this->selectedOrder) {
            // Load suppliers
            $this->suppliers = Supplier::where('active', true)->orderBy('name')->get();

            // Initialize itemSuppliers array
            $this->itemSuppliers = [];
            foreach ($this->selectedOrder->order_items as $item) {
                $this->itemSuppliers[$item->id] = null;
            }

            $this->showItemsModal = true;
            $this->dispatch('modal-show', 'viewOrderItemsModal');
        } else {
            $this->dispatch('error', 'Order not found.');
        }
    }

    public function closeModal()
    {
        $this->showItemsModal = false;
        $this->selectedOrder = null;
        $this->itemSuppliers = [];
        $this->dispatch('modal-hide', 'viewOrderItemsModal');
    }

    public function resetFields()
    {
        $this->showItemsModal = false;
        $this->selectedOrder = null;
        $this->itemSuppliers = [];
    }

    public function submitRequisition()
    {
        if (!$this->selectedOrder) {
            return $this->dispatch('error', 'No order selected.');
        }

        if ($this->selectedOrder->status !== 'approved') {
            return $this->dispatch('error', 'Only approved orders can be submitted for purchase requisition.');
        }

        // Filter out items without suppliers
        $itemsWithSuppliers = array_filter($this->itemSuppliers, function($supplierId) {
            return $supplierId !== null && $supplierId !== '';
        });

        if (empty($itemsWithSuppliers)) {
            return $this->dispatch('error', 'Please select at least one item with a supplier.');
        }

        // Group items by supplier
        $groupedBySupplier = [];
        foreach ($itemsWithSuppliers as $itemId => $supplierId) {
            if (!isset($groupedBySupplier[$supplierId])) {
                $groupedBySupplier[$supplierId] = [];
            }
            $groupedBySupplier[$supplierId][] = $itemId;
        }

        DB::beginTransaction();
        try {
            $createdPRs = [];

            // Create a separate PR for each supplier
            foreach ($groupedBySupplier as $supplierId => $itemIds) {
                $purchaseRequisition = PurchaseRequisition::create([
                    'store_order_id' => $this->selectedOrder->id,
                    'store_requesting_id' => $this->selectedOrder->dept_ordering_id,
                    'supplier_id' => $supplierId,
                    'requisition_description' => $this->selectedOrder->order_description ?? '',
                    'status' => 'pending',
                    'current_approval_level' => 1,
                    'created_by' => auth()->id(),
                ]);

                // Add items for this supplier
                $selectedOrderItems = $this->selectedOrder->order_items()
                    ->whereIn('id', $itemIds)
                    ->get();

                foreach ($selectedOrderItems as $orderItem) {
                    PurchaseRequisitionItem::create([
                        'purchase_requisition_id' => $purchaseRequisition->id,
                        'item_id' => $orderItem->item_id,
                        'units' => $orderItem->units,
                        'itemperunit' => $orderItem->itemperunit,
                        'quantity' => $orderItem->units * $orderItem->itemperunit,
                        'buying_price' => 0,
                        'subtotal' => 0,
                        'remarks' => $orderItem->remarks,
                    ]);
                }

                $createdPRs[] = $purchaseRequisition->id;
            }

            DB::commit();

            $this->closeModal();

            $prCount = count($createdPRs);
            $message = $prCount === 1
                ? 'Purchase requisition created successfully!'
                : "{$prCount} purchase requisitions created successfully!";

            $this->dispatch('success', $message);

            // Redirect to purchase requisitions list or first PR
            if ($prCount === 1) {
                return redirect()->route('purchaserequisitions.view', ['id' => $createdPRs[0]]);
            } else {
                return redirect()->route('purchaserequisitions');
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to create purchase requisition: ' . $th->getMessage());
        }
    }

    public function getOrderItems()
    {
        if (!$this->selectedOrder) {
            return [];
        }

        $items = [];
        foreach ($this->selectedOrder->order_items as $itm) {
            $sel = new stdClass;
            $sel->id = $itm->id;
            $sel->item_name = $itm->item->name ?? 'Unknown';
            $sel->item_code = $itm->item->code ?? '';
            $sel->unit = $itm->item->unit ?? '';
            $sel->units = $itm->units;
            $sel->per_unit = $itm->itemperunit;
            $sel->quantity = $itm->units * $itm->itemperunit;
            $sel->remarks = $itm->remarks;
            $items[] = $sel;
        }
        return $items;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function getSelectedItemsCount()
    {
        return count(array_filter($this->itemSuppliers, function($supplierId) {
            return $supplierId !== null && $supplierId !== '';
        }));
    }

    public function getGroupedBySupplier()
    {
        $grouped = [];
        foreach ($this->itemSuppliers as $itemId => $supplierId) {
            if ($supplierId !== null && $supplierId !== '') {
                if (!isset($grouped[$supplierId])) {
                    $grouped[$supplierId] = [
                        'supplier' => $this->suppliers->firstWhere('id', $supplierId),
                        'items' => []
                    ];
                }
                $grouped[$supplierId]['items'][] = $itemId;
            }
        }
        return $grouped;
    }
}

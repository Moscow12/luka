<?php

namespace App\Livewire\StorageAndSupply\StoreOrder;

use App\Models\{StoreOrder, StoreOrderItem, UserApproval, DocumentApprovalLevel};
use Illuminate\Support\Facades\DB;
use Livewire\{Component, WithPagination};
use stdClass;

class PendingOrders extends Component
{
    use WithPagination;

    public $selectedOrder = null;
    public $showModal = false;
    public $filter_status = 'pending';
    public $search = '';
    public $canApprove = false;
    public $editingItem = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'editingItem.units' => 'required|numeric|min:1',
        'editingItem.itemperunit' => 'required|numeric|min:1',
        'editingItem.remarks' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->checkApprovalPermission();
    }

    public function render()
    {
        $orders = StoreOrder::with(['dept_ordering', 'requester'])
            ->where('status', $this->filter_status)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('dept_ordering', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('requester', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhere('id', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.storage-and-supply.store-order.pending-orders', [
            'orders' => $orders,
        ]);
    }

    public function checkApprovalPermission()
    {
        // Check if current user has approval permission for store orders
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'store_order');
        })->where('user_id', auth()->id())->exists();
    }

    public function viewOrder($id)
    {
        $this->selectedOrder = StoreOrder::with([
            'order_items.item',
            'dept_ordering',
            'requester',
            'approver'
        ])->find($id);

        if ($this->selectedOrder) {
            $this->showModal = true;
            $this->dispatch('modal-show', 'viewOrderModal');
        } else {
            $this->dispatch('error', 'Order not found.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedOrder = null;
        $this->dispatch('modal-hide', 'viewOrderModal');
    }

    public function resetFields()
    {
        $this->showModal = false;
        $this->selectedOrder = null;
    }

    public function approveOrder($id)
    {
        $order = StoreOrder::find($id);

        if (!$order) {
            return $this->dispatch('error', 'Order not found.');
        }

        if (!$order->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($order->status !== 'pending') {
            return $this->dispatch('error', 'Only pending orders can be approved.');
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $order->approval_history ?? [];
            $history[] = [
                'level' => $order->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $order->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $order->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $order->current_approval_level = $nextLevel->label;
                $order->save();

                $approvers = $order->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "Order moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - mark as approved
                $order->status = 'approved';
                $order->approved_by = auth()->id();
                $order->save();

                DB::commit();
                $this->dispatch('success', 'Order fully approved successfully!');
            }

            $this->closeModal();
            $this->render();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve order: ' . $th->getMessage());
        }
    }

    public function rejectOrder($id)
    {
        $order = StoreOrder::find($id);

        if (!$order) {
            return $this->dispatch('error', 'Order not found.');
        }

        if (!$order->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($order->status !== 'pending') {
            return $this->dispatch('error', 'Only pending orders can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $order->approval_history ?? [];
            $history[] = [
                'level' => $order->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];
            $order->approval_history = $history;

            $order->status = 'rejected';
            $order->approved_by = auth()->id();
            $order->save();

            DB::commit();
            $this->dispatch('success', 'Order rejected.');
            $this->closeModal();
            $this->render();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject order: ' . $th->getMessage());
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
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
            $sel->current_balance = itemBalance($this->selectedOrder->dept_ordering_id, $itm->item_id);
            $items[] = $sel;
        }
        return $items;
    }

    public function editOrderItem($id)
    {
        if (!$item = StoreOrderItem::find($id)) {
            return $this->dispatch('error', 'Item not found.');
        }

        $this->editingItem = $item;
    }

    public function updateOrderItem()
    {
        if (!$this->editingItem) {
            return $this->dispatch('error', 'No item selected for editing.');
        }

        $this->validate();

        DB::beginTransaction();
        try {
            $this->editingItem->save();
            DB::commit();
            $this->editingItem = null;
            $this->viewOrder($this->selectedOrder->id);
            $this->dispatch('success', 'Item updated successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to update item: ' . $th->getMessage());
        }
    }

    public function cancelEditItem()
    {
        $this->editingItem = null;
    }

    public function deleteOrderItem($id)
    {
        if (!$item = StoreOrderItem::find($id)) {
            return $this->dispatch('error', 'Item not found.');
        }

        if ($this->selectedOrder->status !== 'pending') {
            return $this->dispatch('error', 'Only items in pending orders can be deleted.');
        }

        DB::beginTransaction();
        try {
            $orderId = $item->order_id;
            $order = StoreOrder::find($orderId);

            $item->delete();

            // If no items left, delete the order
            if ($order && $order->order_items()->count() == 0) {
                $order->delete();
                $this->closeModal();
                $this->dispatch('success', 'Item and empty order deleted.');
            } else {
                $this->viewOrder($orderId);
                $this->dispatch('success', 'Item deleted successfully.');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to delete item: ' . $th->getMessage());
        }
    }
}

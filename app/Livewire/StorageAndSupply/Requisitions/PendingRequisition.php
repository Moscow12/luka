<?php

namespace App\Livewire\StorageAndSupply\Requisitions;

use App\Models\{StoreRequisition, StoreRequisitionItem, UserApproval, DocumentApprovalLevel};
use Illuminate\Support\Facades\DB;
use Livewire\{Component, WithPagination};
use stdClass;

class PendingRequisition extends Component
{
    use WithPagination;

    public $selectedRequisition = null;
    public $showModal = false;
    public $filter_status = 'pending';
    public $search = '';
    public $canApprove = false;
    public $editingItem = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'editingItem.quantity' => 'required|numeric|min:1',
        'editingItem.remarks' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->checkApprovalPermission();
    }

    public function render()
    {
        $requisitions = StoreRequisition::with(['dept_requesting', 'dept_issuing', 'requester'])
            ->where('status', $this->filter_status)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('dept_requesting', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('dept_issuing', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('requester', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhere('id', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.storage-and-supply.requisitions.pending-requisition', [
            'requisitions' => $requisitions,
        ]);
    }

    public function checkApprovalPermission()
    {
        // Check if current user has approval permission for requisitions
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'requisition');
        })->where('user_id', auth()->id())->exists();
    }

    public function viewRequisition($id)
    {
        $this->selectedRequisition = StoreRequisition::with([
            'requisition_items.item',
            'dept_requesting',
            'dept_issuing',
            'requester',
            'approver'
        ])->find($id);

        if ($this->selectedRequisition) {
            $this->showModal = true;
            $this->dispatch('modal-show', 'viewRequisitionModal');
        } else {
            $this->dispatch('error', 'Requisition not found.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedRequisition = null;
        $this->dispatch('modal-hide', 'viewRequisitionModal');
    }

    public function resetFields()
    {
        $this->showModal = false;
        $this->selectedRequisition = null;
    }

    public function approveRequisition($id)
    {
        $requisition = StoreRequisition::find($id);

        if (!$requisition) {
            return $this->dispatch('error', 'Requisition not found.');
        }

        if (!$requisition->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($requisition->status !== 'pending') {
            return $this->dispatch('error', 'Only pending requisitions can be approved.');
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $requisition->approval_history ?? [];
            $history[] = [
                'level' => $requisition->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $requisition->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $requisition->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $requisition->current_approval_level = $nextLevel->label;
                $requisition->save();

                $approvers = $requisition->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "Requisition moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - mark as approved
                $requisition->status = 'approved';
                $requisition->approved_by = auth()->id();
                $requisition->save();

                DB::commit();
                $this->dispatch('success', 'Requisition fully approved successfully!');
            }

            $this->closeModal();
            $this->render();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve requisition: ' . $th->getMessage());
        }
    }

    public function rejectRequisition($id)
    {
        $requisition = StoreRequisition::find($id);

        if (!$requisition) {
            return $this->dispatch('error', 'Requisition not found.');
        }

        if (!$requisition->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($requisition->status !== 'pending') {
            return $this->dispatch('error', 'Only pending requisitions can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $requisition->approval_history ?? [];
            $history[] = [
                'level' => $requisition->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];
            $requisition->approval_history = $history;

            $requisition->status = 'rejected';
            $requisition->approved_by = auth()->id();
            $requisition->save();

            DB::commit();
            $this->dispatch('success', 'Requisition rejected.');
            $this->closeModal();
            $this->render();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject requisition: ' . $th->getMessage());
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

    public function getRequisitionItems()
    {
        if (!$this->selectedRequisition) {
            return [];
        }

        $items = [];
        foreach ($this->selectedRequisition->requisition_items as $itm) {
            $sel = new stdClass;
            $sel->id = $itm->id;
            $sel->item_name = $itm->item->name ?? 'Unknown';
            $sel->item_code = $itm->item->code ?? '';
            $sel->unit = $itm->item->unit ?? '';
            $sel->quantity = $itm->quantity;
            $sel->remarks = $itm->remarks;
            $sel->balance_requesting = itemBalance($this->selectedRequisition->dept_reqesting_id, $itm->item_id);
            $sel->balance_issuing = itemBalance($this->selectedRequisition->dept_issueing_id, $itm->item_id);
            $items[] = $sel;
        }
        return $items;
    }

    public function editRequisitionItem($id)
    {
        if (!$item = StoreRequisitionItem::find($id)) {
            return $this->dispatch('error', 'Item not found.');
        }

        $this->editingItem = $item;
    }

    public function updateRequisitionItem()
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
            $this->viewRequisition($this->selectedRequisition->id);
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

    public function deleteRequisitionItem($id)
    {
        if (!$item = StoreRequisitionItem::find($id)) {
            return $this->dispatch('error', 'Item not found.');
        }

        if ($this->selectedRequisition->status !== 'pending') {
            return $this->dispatch('error', 'Only items in pending requisitions can be deleted.');
        }

        DB::beginTransaction();
        try {
            $requisitionId = $item->requisition_id;
            $requisition = StoreRequisition::find($requisitionId);

            $item->delete();

            // If no items left, delete the requisition
            if ($requisition && $requisition->requisition_items()->count() == 0) {
                $requisition->delete();
                $this->closeModal();
                $this->dispatch('success', 'Item and empty requisition deleted.');
            } else {
                $this->viewRequisition($requisitionId);
                $this->dispatch('success', 'Item deleted successfully.');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to delete item: ' . $th->getMessage());
        }
    }
}

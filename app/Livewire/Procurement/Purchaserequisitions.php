<?php

namespace App\Livewire\Procurement;

use App\Models\{PurchaseRequisition, UserApproval, LocalPurchaseOrder};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;

class Purchaserequisitions extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = 'pending';
    public $selectedPR = null;
    public $showItemsModal = false;
    public $canApprove = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->checkApprovalPermission();
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'purchase_requisition');
        })->where('user_id', auth()->id())->exists();
    }

    public function render()
    {
        $purchaseRequisitions = PurchaseRequisition::with(['store_requesting', 'supplier', 'requester', 'approver'])
            ->when($this->filter_status !== 'all', function($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('store_requesting', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhere('id', 'like', "%{$this->search}%")
                      ->orWhere('requisition_description', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.procurement.purchaserequisitions', [
            'purchaseRequisitions' => $purchaseRequisitions,
        ]);
    }

    public function viewPRItems($id)
    {
        $this->selectedPR = PurchaseRequisition::with([
            'requisition_items.item',
            'store_requesting',
            'supplier',
            'requester',
            'approver'
        ])->find($id);

        if ($this->selectedPR) {
            $this->showItemsModal = true;
            $this->dispatch('modal-show', 'viewPRItemsModal');
        } else {
            $this->dispatch('error', 'Purchase requisition not found.');
        }
    }

    public function closeModal()
    {
        $this->showItemsModal = false;
        $this->selectedPR = null;
        $this->dispatch('modal-hide', 'viewPRItemsModal');
    }

    public function getPRItems()
    {
        if (!$this->selectedPR) {
            return [];
        }

        return $this->selectedPR->requisition_items->map(function($item) {
            return (object)[
                'id' => $item->id,
                'item_name' => $item->item->name ?? 'Unknown',
                'item_code' => $item->item->code ?? '',
                'unit' => $item->item->unit ?? '',
                'units' => $item->units,
                'per_unit' => $item->itemperunit,
                'quantity' => $item->quantity,
                'buying_price' => $item->buying_price,
                'subtotal' => $item->subtotal,
                'remarks' => $item->remarks,
            ];
        })->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->showItemsModal = false;
        $this->selectedPR = null;
    }

    public function rollbackPR($prId)
    {
        $pr = PurchaseRequisition::find($prId);

        if (!$pr) {
            return $this->dispatch('error', 'Purchase requisition not found.');
        }

        // Check if user has approval permission or is the creator
        $canRollback = $this->canApprove || $pr->created_by === auth()->id();

        if (!$canRollback) {
            return $this->dispatch('error', 'You do not have permission to rollback this PR.');
        }

        // Only allow rollback for approved or rejected PRs
        if (!in_array($pr->status, ['approved', 'rejected'])) {
            return $this->dispatch('error', 'Only approved or rejected PRs can be rolled back.');
        }

        // Check if there's an associated LPO
        $lpo = LocalPurchaseOrder::where('purchase_requisition_id', $pr->id)->first();
        if ($lpo) {
            return $this->dispatch('error', 'Cannot rollback PR. An LPO has already been created. Please rollback the LPO first.');
        }

        DB::beginTransaction();
        try {
            // Record rollback in history
            $history = $pr->approval_history ?? [];
            $history[] = [
                'level' => $pr->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rolled_back',
                'previous_status' => $pr->status
            ];

            $pr->approval_history = $history;
            $pr->status = 'pending';
            $pr->current_approval_level = 1;
            $pr->approved_by = null;
            $pr->save();

            DB::commit();
            $this->dispatch('success', 'Purchase requisition rolled back to pending status.');
            $this->closeModal();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to rollback: ' . $th->getMessage());
        }
    }

    public function getLPOStatus($prId)
    {
        $lpo = LocalPurchaseOrder::where('purchase_requisition_id', $prId)->first();
        return $lpo ? (object)[
            'exists' => true,
            'number' => $lpo->lpo_number,
            'status' => $lpo->status,
            'id' => $lpo->id
        ] : (object)['exists' => false];
    }
}

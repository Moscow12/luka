<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{GoodsReceivedNote, UserApproval};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;

class GrnList extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = 'pending';
    public $selectedGRN = null;
    public $showPreviewModal = false;
    public $canApprove = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->checkApprovalPermission();
    }

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $grns = GoodsReceivedNote::with([
                'local_purchase_order.purchase_requisition.store_order',
                'local_purchase_order.purchase_requisition.store_requesting',
                'local_purchase_order.supplier',
                'creator',
                'approver',
                'grn_items.batches'
            ])
            ->when($selectedSubdepartmentId, function($query) use ($selectedSubdepartmentId) {
                $query->whereHas('local_purchase_order.purchase_requisition', function ($sq) use ($selectedSubdepartmentId) {
                    $sq->where('store_requesting_id', $selectedSubdepartmentId);
                });
            })
            ->when($this->filter_status !== 'all', function($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('grn_number', 'like', "%{$this->search}%")
                      ->orWhere('delivery_note_number', 'like', "%{$this->search}%")
                      ->orWhere('invoice_number', 'like', "%{$this->search}%")
                      ->orWhereHas('local_purchase_order', fn($sq) =>
                          $sq->where('lpo_number', 'like', "%{$this->search}%")
                      )
                      ->orWhereHas('local_purchase_order.supplier', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.storage-and-supply.grn.grn-list', [
            'grns' => $grns,
        ]);
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'grn_against_purchases_order');
        })->where('user_id', auth()->id())->exists();
    }

    public function viewGRN($id)
    {
        $this->selectedGRN = GoodsReceivedNote::with([
            'grn_items.item',
            'grn_items.batches',
            'local_purchase_order.supplier',
            'local_purchase_order.purchase_requisition.store_order',
            'local_purchase_order.purchase_requisition.store_requesting',
            'creator',
            'approver'
        ])->find($id);

        if ($this->selectedGRN) {
            $this->showPreviewModal = true;
            $this->dispatch('modal-show', 'grnPreviewModal');
        } else {
            $this->dispatch('error', 'GRN not found.');
        }
    }

    public function closeModal()
    {
        $this->showPreviewModal = false;
        $this->selectedGRN = null;
        $this->dispatch('modal-hide', 'grnPreviewModal');
    }

    public function approveGRN()
    {
        if (!$this->selectedGRN) {
            return $this->dispatch('error', 'GRN not found.');
        }

        if (!$this->selectedGRN->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($this->selectedGRN->status !== 'pending') {
            return $this->dispatch('error', 'Only pending GRNs can be approved.');
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $this->selectedGRN->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedGRN->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $this->selectedGRN->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $this->selectedGRN->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $this->selectedGRN->current_approval_level = $nextLevel->label;
                $this->selectedGRN->save();

                $approvers = $this->selectedGRN->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "GRN moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - add to stock ledger
                $this->selectedGRN->status = 'approved';
                $this->selectedGRN->approved_by = auth()->id();
                $this->selectedGRN->approved_at = now();
                $this->selectedGRN->save();

                // Add to stock ledger
                $this->selectedGRN->addToStockLedger();

                DB::commit();
                $this->dispatch('success', 'GRN fully approved and added to stock ledger!');
            }

            $this->closeModal();
            $this->render();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve: ' . $th->getMessage());
        }
    }

    public function rejectGRN()
    {
        if (!$this->selectedGRN) {
            return $this->dispatch('error', 'GRN not found.');
        }

        if (!$this->selectedGRN->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($this->selectedGRN->status !== 'pending') {
            return $this->dispatch('error', 'Only pending GRNs can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $this->selectedGRN->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedGRN->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];

            $this->selectedGRN->approval_history = $history;
            $this->selectedGRN->status = 'rejected';
            $this->selectedGRN->save();

            DB::commit();
            $this->dispatch('success', 'GRN rejected.');
            $this->closeModal();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject: ' . $th->getMessage());
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

    public function resetFields()
    {
        $this->showPreviewModal = false;
        $this->selectedGRN = null;
    }
}

<?php

namespace App\Livewire\Procurement;

use App\Models\{LocalPurchaseOrder, LocalPurchaseOrderItem, PurchaseRequisition, UserApproval};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;

class Lpo extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = 'pending';
    public $selectedLPO = null;
    public $showPreviewModal = false;
    public $canApprove = false;

    // For creating LPO from PR
    public $prId = null;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Check if PR ID is provided via URL parameter
        if (request()->has('pr')) {
            $this->prId = request('pr');
            $this->createLPOFromPR();
        }

        $this->checkApprovalPermission();
    }

    public function render()
    {
        $lpos = LocalPurchaseOrder::with(['supplier', 'creator', 'approver', 'purchase_requisition'])
            ->when($this->filter_status !== 'all', function($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('lpo_number', 'like', "%{$this->search}%")
                      ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                      ->orWhere('notes', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.procurement.lpo', [
            'lpos' => $lpos,
        ]);
    }

    public function createLPOFromPR()
    {
        if (!$this->prId) {
            return;
        }

        $pr = PurchaseRequisition::with(['requisition_items.item', 'supplier'])->find($this->prId);

        if (!$pr) {
            $this->dispatch('error', 'Purchase Requisition not found.');
            return;
        }

        if ($pr->status !== 'approved') {
            $this->dispatch('error', 'Only approved purchase requisitions can create LPO.');
            return redirect()->route('purchaserequisitions');
        }

        // Check if LPO already exists for this PR
        $existingLPO = LocalPurchaseOrder::where('purchase_requisition_id', $pr->id)->first();
        if ($existingLPO) {
            $this->dispatch('info', 'LPO already exists for this purchase requisition.');
            return redirect()->route('lpo');
        }

        DB::beginTransaction();
        try {
            // Create LPO
            $lpo = LocalPurchaseOrder::create([
                'purchase_requisition_id' => $pr->id,
                'supplier_id' => $pr->supplier_id,
                'lpo_number' => LocalPurchaseOrder::generateLPONumber(),
                'lpo_date' => now(),
                'delivery_date' => now()->addDays(14), // Default 14 days
                'total_amount' => 0, // Will be calculated
                'status' => 'pending',
                'current_approval_level' => 1,
                'created_by' => auth()->id(),
            ]);

            // Create LPO items from PR items
            $totalAmount = 0;
            foreach ($pr->requisition_items as $prItem) {
                $buyingPrice = floatval($prItem->buying_price ?? 0);
                $quantity = intval($prItem->quantity ?? 0);
                $itemTotal = $buyingPrice * $quantity;
                $totalAmount += $itemTotal;

                LocalPurchaseOrderItem::create([
                    'local_purchase_order_id' => $lpo->id,
                    'item_id' => $prItem->item_id,
                    'quantity' => $quantity,
                    'unit_price' => $buyingPrice,
                    'total_price' => $itemTotal,
                    'specifications' => $prItem->remarks,
                    'created_by' => auth()->id(),
                ]);
            }

            // Update total amount
            $lpo->update(['total_amount' => $totalAmount]);

            DB::commit();

            $this->dispatch('success', 'LPO created successfully!');
            $this->prId = null;

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to create LPO: ' . $th->getMessage());
        }
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'local_purchase_order');
        })->where('user_id', auth()->id())->exists();
    }

    public function viewLPO($id)
    {
        $this->selectedLPO = LocalPurchaseOrder::with([
            'lpo_items.item',
            'supplier',
            'creator',
            'approver',
            'purchase_requisition'
        ])->find($id);

        if ($this->selectedLPO) {
            $this->showPreviewModal = true;
            $this->dispatch('modal-show', 'lpoPreviewModal');
        } else {
            $this->dispatch('error', 'LPO not found.');
        }
    }

    public function closeModal()
    {
        $this->showPreviewModal = false;
        $this->selectedLPO = null;
        $this->dispatch('modal-hide', 'lpoPreviewModal');
    }

    public function approveLPO()
    {
        if (!$this->selectedLPO) {
            return $this->dispatch('error', 'LPO not found.');
        }

        if (!$this->selectedLPO->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($this->selectedLPO->status !== 'pending') {
            return $this->dispatch('error', 'Only pending LPOs can be approved.');
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $this->selectedLPO->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedLPO->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $this->selectedLPO->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $this->selectedLPO->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $this->selectedLPO->current_approval_level = $nextLevel->label;
                $this->selectedLPO->save();

                $approvers = $this->selectedLPO->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "LPO moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval
                $this->selectedLPO->status = 'approved';
                $this->selectedLPO->approved_by = auth()->id();
                $this->selectedLPO->save();

                DB::commit();
                $this->dispatch('success', 'LPO fully approved!');
            }

            $this->closeModal();
            $this->render();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve: ' . $th->getMessage());
        }
    }

    public function rejectLPO()
    {
        if (!$this->selectedLPO) {
            return $this->dispatch('error', 'LPO not found.');
        }

        if (!$this->selectedLPO->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($this->selectedLPO->status !== 'pending') {
            return $this->dispatch('error', 'Only pending LPOs can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $this->selectedLPO->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedLPO->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];

            $this->selectedLPO->approval_history = $history;
            $this->selectedLPO->status = 'rejected';
            $this->selectedLPO->save();

            DB::commit();
            $this->dispatch('success', 'LPO rejected.');
            $this->closeModal();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject: ' . $th->getMessage());
        }
    }

    public function rollbackLPO()
    {
        if (!$this->selectedLPO) {
            return $this->dispatch('error', 'LPO not found.');
        }

        // Check if user has approval permission or is the creator
        $canRollback = $this->canApprove || $this->selectedLPO->created_by === auth()->id();

        if (!$canRollback) {
            return $this->dispatch('error', 'You do not have permission to rollback this LPO.');
        }

        // Only allow rollback for approved or rejected LPOs
        if (!in_array($this->selectedLPO->status, ['approved', 'rejected'])) {
            return $this->dispatch('error', 'Only approved or rejected LPOs can be rolled back for modification.');
        }

        DB::beginTransaction();
        try {
            // Record rollback in history
            $history = $this->selectedLPO->approval_history ?? [];
            $history[] = [
                'level' => $this->selectedLPO->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rolled_back',
                'previous_status' => $this->selectedLPO->status
            ];

            $this->selectedLPO->approval_history = $history;
            $this->selectedLPO->status = 'pending';
            $this->selectedLPO->current_approval_level = 1;
            $this->selectedLPO->approved_by = null;
            $this->selectedLPO->save();

            // Also rollback the associated PR if it exists
            if ($this->selectedLPO->purchase_requisition_id) {
                $pr = PurchaseRequisition::find($this->selectedLPO->purchase_requisition_id);
                if ($pr && $pr->status == 'approved') {
                    $prHistory = $pr->approval_history ?? [];
                    $prHistory[] = [
                        'level' => $pr->current_approval_level,
                        'approved_by' => auth()->id(),
                        'approved_by_name' => auth()->user()->name,
                        'approved_at' => now()->toDateTimeString(),
                        'action' => 'rolled_back_via_lpo',
                        'previous_status' => $pr->status,
                        'lpo_number' => $this->selectedLPO->lpo_number
                    ];

                    $pr->approval_history = $prHistory;
                    $pr->status = 'pending';
                    $pr->current_approval_level = 1;
                    $pr->approved_by = null;
                    $pr->save();
                }
            }

            DB::commit();
            $this->dispatch('success', 'LPO and associated PR rolled back to pending status for modification.');
            $this->closeModal();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to rollback: ' . $th->getMessage());
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
        $this->selectedLPO = null;
    }
}

<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{GrnWithoutLpo, UserApproval, DocumentApprovalLevel, User};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ViewGrnWithoutLpo extends Component
{
    public $grnId;
    public $grn;
    public $canApprove = false;
    public $approvalLevels = [];
    public $currentLevelApprovers = [];

    public function mount($grnId)
    {
        $this->grnId = $grnId;
        $this->loadGRN();
        $this->checkApprovalPermission();
        $this->loadApprovalLevels();
    }

    public function loadGRN()
    {
        $this->grn = GrnWithoutLpo::with([
            'supplier',
            'subdepartment',
            'items.item',
            'creator',
            'approver'
        ])->findOrFail($this->grnId);
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'grn_without_purchases_order');
        })->where('user_id', auth()->id())->exists();
    }

    public function loadApprovalLevels()
    {
        if ($this->grn->status !== 'pending') {
            return;
        }

        // Get all approval levels for this document type
        $levels = DocumentApprovalLevel::where('document_type', 'grn_without_purchases_order')
            ->orderBy('label')
            ->get();

        $this->approvalLevels = $levels->map(function ($level) {
            // Get approvers for this level
            $approvers = User::whereHas('approvals', function ($query) use ($level) {
                $query->where('approval_level_id', $level->id);
            })->get(['id', 'name']);

            // Check if this level is completed
            $isCompleted = false;
            if ($this->grn->approval_history) {
                foreach ($this->grn->approval_history as $history) {
                    if (isset($history['level']) && $history['level'] == $level->label && $history['action'] == 'approved') {
                        $isCompleted = true;
                        break;
                    }
                }
            }

            return [
                'id' => $level->id,
                'label' => $level->label,
                'level_title_id' => $level->level_title_id,
                'is_current' => $level->label == $this->grn->current_approval_level,
                'is_completed' => $isCompleted,
                'approvers' => $approvers->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'can_approve' => $u->id == auth()->id() && $level->label == $this->grn->current_approval_level
                ])
            ];
        })->toArray();

        // Load current level approvers
        if ($this->grn->current_approval_level) {
            $this->currentLevelApprovers = $this->grn->getCurrentLevelApprovers();
        }
    }

    public function approveGRN()
    {
        if (!$this->grn->canUserApprove(auth()->id())) {
            $this->dispatch('error', 'You do not have permission to approve at this level.');
            return;
        }

        if ($this->grn->status !== 'pending') {
            $this->dispatch('error', 'Only pending GRNs can be approved.');
            return;
        }

        DB::beginTransaction();
        try {
            // Record approval in history
            $history = $this->grn->approval_history ?? [];
            $history[] = [
                'level' => $this->grn->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $this->grn->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $this->grn->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $this->grn->current_approval_level = $nextLevel->label;
                $this->grn->save();

                $approvers = $this->grn->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->loadGRN();
                $this->loadApprovalLevels();
                $this->dispatch('success', "GRN moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - add to stock ledger
                $this->grn->status = 'approved';
                $this->grn->approved_by = auth()->id();
                $this->grn->approved_at = now();
                $this->grn->save();

                // Add to stock ledger
                $this->grn->addToStockLedger();

                DB::commit();
                session()->flash('success', 'GRN fully approved and added to stock ledger!');

                return redirect()->route('grn-without-lpo');
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve: ' . $th->getMessage());
        }
    }

    public function rejectGRN()
    {
        if (!$this->grn->canUserApprove(auth()->id())) {
            $this->dispatch('error', 'You do not have permission to reject at this level.');
            return;
        }

        if ($this->grn->status !== 'pending') {
            $this->dispatch('error', 'Only pending GRNs can be rejected.');
            return;
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $this->grn->approval_history ?? [];
            $history[] = [
                'level' => $this->grn->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];

            $this->grn->approval_history = $history;
            $this->grn->status = 'rejected';
            $this->grn->save();

            DB::commit();
            session()->flash('success', 'GRN rejected.');

            return redirect()->route('grn-without-lpo');

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.storage-and-supply.grn.view-grn-without-lpo');
    }
}

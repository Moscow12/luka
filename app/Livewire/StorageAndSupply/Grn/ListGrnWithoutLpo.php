<?php

namespace App\Livewire\StorageAndSupply\Grn;

use App\Models\{GrnWithoutLpo, Supplier, Subdepartment};
use Livewire\{Component, WithPagination};
use Illuminate\Support\Facades\DB;

class ListGrnWithoutLpo extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = 'all';
    public $filter_date_from = '';
    public $filter_date_to = '';
    public $filter_supplier = '';

    public $showViewModal = false;
    public $viewingGrn = null;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $grns = GrnWithoutLpo::with([
                'supplier',
                'subdepartment',
                'creator',
                'approver',
                'items.item'
            ])
            ->when($selectedSubdepartmentId, function($query) use ($selectedSubdepartmentId) {
                $query->where('subdepartment_id', $selectedSubdepartmentId);
            })
            ->when($this->filter_status !== 'all', function($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('grn_number', 'like', "%{$this->search}%")
                      ->orWhere('delivery_note_number', 'like', "%{$this->search}%")
                      ->orWhere('invoice_number', 'like', "%{$this->search}%")
                      ->orWhereHas('supplier', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->when($this->filter_date_from, function ($query) {
                $query->whereDate('delivery_date', '>=', $this->filter_date_from);
            })
            ->when($this->filter_date_to, function ($query) {
                $query->whereDate('delivery_date', '<=', $this->filter_date_to);
            })
            ->when($this->filter_supplier, function ($query) {
                $query->where('supplier_id', $this->filter_supplier);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.storage-and-supply.grn.list-grn-without-lpo', [
            'grns' => $grns,
            'suppliers' => $suppliers,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
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

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filter_status = 'all';
        $this->filter_date_from = '';
        $this->filter_date_to = '';
        $this->filter_supplier = '';
        $this->resetPage();
    }

    public function viewGrn($id)
    {
        $this->viewingGrn = GrnWithoutLpo::with(['supplier', 'subdepartment', 'items.item', 'creator', 'approver'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingGrn = null;
    }

    public function approveGrn($id)
    {
        DB::beginTransaction();
        try {
            $grn = GrnWithoutLpo::findOrFail($id);

            // Check if user can approve
            if (!$grn->canUserApprove(auth()->id())) {
                $this->dispatch('error', 'You do not have permission to approve this GRN at the current level.');
                return;
            }

            // Get next approval level
            $nextLevel = $grn->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next level
                $grn->current_approval_level = $nextLevel->label;
                $grn->save();

                $this->dispatch('success', 'GRN moved to next approval level successfully!');
            } else {
                // Final approval - mark as approved and add to stock ledger
                $grn->status = 'approved';
                $grn->approved_by = auth()->id();
                $grn->approved_at = now();
                $grn->save();

                // Add to stock ledger
                $grn->addToStockLedger();

                $this->dispatch('success', 'GRN approved successfully and added to stock ledger!');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve GRN: ' . $e->getMessage());
        }
    }

    public function rejectGrn($id)
    {
        try {
            $grn = GrnWithoutLpo::findOrFail($id);

            // Check if user can approve (same permission for reject)
            if (!$grn->canUserApprove(auth()->id())) {
                $this->dispatch('error', 'You do not have permission to reject this GRN.');
                return;
            }

            $grn->status = 'rejected';
            $grn->approved_by = auth()->id();
            $grn->approved_at = now();
            $grn->save();

            $this->dispatch('success', 'GRN rejected successfully!');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reject GRN: ' . $e->getMessage());
        }
    }

    public function deleteGrn($id)
    {
        try {
            $grn = GrnWithoutLpo::findOrFail($id);

            // Only allow deletion of pending GRNs by creator
            if ($grn->status !== 'pending') {
                $this->dispatch('error', 'Only pending GRNs can be deleted.');
                return;
            }

            if ($grn->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
                $this->dispatch('error', 'You can only delete your own GRNs.');
                return;
            }

            $grn->delete();
            $this->dispatch('success', 'GRN deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete GRN: ' . $e->getMessage());
        }
    }
}

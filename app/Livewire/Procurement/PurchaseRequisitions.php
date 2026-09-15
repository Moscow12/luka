<?php

namespace App\Livewire\Procurement;

use App\Models\LocalPurchaseOrder;
use App\Models\LocalPurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\vendors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PurchaseRequisitions extends Component
{
    use WithFileUploads, WithPagination;

    public $activeTab = 'draft';

    public $search = '';

    public $viewingRequisition = null;

    public $showViewModal = false;

    public $editingItemId = null;

    public $editSupplierId = '';

    public $editPrice = '';

    public $editQuantity = '';

    public $quotation1File;

    public $quotation2File;

    public $quotation3File;

    public $showQuotationModal = false;

    public $itemEdits = [];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openViewModal($id)
    {
        $this->viewingRequisition = PurchaseRequisition::with([
            'items.storeOrderItem.item',
            'items.storeOrder.department',
            'items.supplier',
            'createdBy',
            'approvedBy',
            'localPurchaseOrder.items',
        ])->findOrFail($id);

        $this->itemEdits = $this->viewingRequisition->items
            ->mapWithKeys(fn (PurchaseRequisitionItem $item) => [
                $item->id => [
                    'status' => $item->status,
                    'remarks' => $item->remarks,
                ],
            ])
            ->toArray();

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingRequisition = null;
        $this->itemEdits = [];
    }

    public function saveItemStatus($purchaseRequisitionItemId)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('approve-requisition'), 403);

        $this->validate([
            "itemEdits.{$purchaseRequisitionItemId}.status" => ['required', 'in:pending,approved,active,rejected'],
            "itemEdits.{$purchaseRequisitionItemId}.remarks" => ['nullable', 'string', 'max:1000'],
        ]);

        $item = PurchaseRequisitionItem::with('purchaseRequisition')->findOrFail($purchaseRequisitionItemId);

        if (! $item->purchaseRequisition->isApproved() || $item->purchaseRequisition->localPurchaseOrder()->exists()) {
            session()->flash('error', 'This item can no longer be updated.');

            return;
        }

        $item->update([
            'status' => $this->itemEdits[$purchaseRequisitionItemId]['status'],
            'remarks' => $this->itemEdits[$purchaseRequisitionItemId]['remarks'] ?: null,
        ]);

        session()->flash('success', 'Item status updated successfully!');

        if ($this->viewingRequisition) {
            $this->openViewModal($item->purchase_requisition_id);
        }
    }

    public function approveRequisition($id)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('approve-requisition'), 403);

        $requisition = PurchaseRequisition::findOrFail($id);

        if (! $requisition->canApprove()) {
            session()->flash('error', 'This requisition cannot be approved.');

            return;
        }

        $requisition->update([
            'status' => PurchaseRequisition::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        session()->flash('success', 'Requisition '.$requisition->requisition_number.' approved successfully!');

        if ($this->viewingRequisition && $this->viewingRequisition->id === $requisition->id) {
            $this->openViewModal($requisition->id);
        }
    }

    public function openQuotationModal($purchaseRequisitionItemId)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('manage-requisition'), 403);

        $item = PurchaseRequisitionItem::with('purchaseRequisition')->findOrFail($purchaseRequisitionItemId);

        if (! $item->purchaseRequisition->isApproved()) {
            session()->flash('error', 'The requisition must be approved before quotations can be filled in.');

            return;
        }

        $this->editingItemId = $item->id;
        $this->editSupplierId = $item->supplier_id ?? '';
        $this->editPrice = $item->price ?? '';
        $this->editQuantity = $item->quantity ?? '';
        $this->quotation1File = null;
        $this->quotation2File = null;
        $this->quotation3File = null;
        $this->resetErrorBag();
        $this->showQuotationModal = true;
    }

    public function closeQuotationModal()
    {
        $this->showQuotationModal = false;
        $this->editingItemId = null;
        $this->quotation1File = null;
        $this->quotation2File = null;
        $this->quotation3File = null;
    }

    public function saveQuotation()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('manage-requisition'), 403);

        $this->validate([
            'editSupplierId' => ['nullable', 'exists:vendors,id'],
            'editPrice' => ['nullable', 'numeric', 'min:0'],
            'editQuantity' => ['nullable', 'numeric', 'min:0'],
            'quotation1File' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'quotation2File' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'quotation3File' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $item = PurchaseRequisitionItem::with('purchaseRequisition')->findOrFail($this->editingItemId);

        if (! $item->purchaseRequisition->isApproved()) {
            session()->flash('error', 'The requisition must be approved before quotations can be filled in.');
            $this->closeQuotationModal();

            return;
        }

        $data = [
            'supplier_id' => $this->editSupplierId ?: null,
            'price' => $this->editPrice !== '' ? $this->editPrice : null,
            'quantity' => $this->editQuantity !== '' ? $this->editQuantity : null,
        ];

        foreach (['quotation1File' => 'quotation1', 'quotation2File' => 'quotation2', 'quotation3File' => 'quotation3'] as $property => $column) {
            $file = $this->{$property};

            if ($file && is_object($file) && method_exists($file, 'store')) {
                if ($item->{$column}) {
                    Storage::disk('public')->delete($item->{$column});
                }

                $data[$column] = $file->store('purchase-requisitions/quotations', 'public');
            }
        }

        $item->update($data);

        session()->flash('success', 'Quotation details saved successfully!');
        $this->closeQuotationModal();

        if ($this->viewingRequisition) {
            $this->openViewModal($item->purchase_requisition_id);
        }
    }

    public function generateLpo($purchaseRequisitionId)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('manage-requisition'), 403);

        $requisition = PurchaseRequisition::with('items')->findOrFail($purchaseRequisitionId);

        if (! $requisition->canGenerateLpo()) {
            session()->flash('error', 'This requisition is not ready for LPO generation. Ensure it is approved and every item marked "Approved" has a supplier, price, and at least one quotation.');

            return;
        }

        DB::transaction(function () use ($requisition) {
            $approvedItems = $requisition->items->where('status', PurchaseRequisitionItem::STATUS_APPROVED);

            $total = $approvedItems->sum(fn (PurchaseRequisitionItem $item) => (float) $item->price * (float) $item->quantity);

            $lpo = LocalPurchaseOrder::create([
                'lpo_number' => LocalPurchaseOrder::generateLpoNumber(),
                'purchase_requisition_id' => $requisition->id,
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'total_amount' => $total,
            ]);

            foreach ($approvedItems as $item) {
                LocalPurchaseOrderItem::create([
                    'local_purchase_order_id' => $lpo->id,
                    'purchase_requisition_item_id' => $item->id,
                    'item_name' => $item->storeOrderItem?->item?->name ?? 'Unknown Item',
                    'supplier_id' => $item->supplier_id,
                    'supplier_name' => $item->supplier?->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'quotation1' => $item->quotation1,
                    'quotation2' => $item->quotation2,
                    'quotation3' => $item->quotation3,
                ]);
            }
        });

        session()->flash('success', 'Local purchase order generated successfully!');

        if ($this->viewingRequisition && $this->viewingRequisition->id === $requisition->id) {
            $this->openViewModal($requisition->id);
        }
    }

    protected function baseQuery()
    {
        return PurchaseRequisition::query()
            ->with(['createdBy', 'approvedBy', 'items', 'localPurchaseOrder'])
            ->when($this->search, fn ($q) => $q->where('requisition_number', 'like', '%'.$this->search.'%'));
    }

    protected function draftCount(): int
    {
        return PurchaseRequisition::where('status', PurchaseRequisition::STATUS_DRAFT)->count();
    }

    protected function approvedCount(): int
    {
        return PurchaseRequisition::where('status', PurchaseRequisition::STATUS_APPROVED)->count();
    }

    public function render()
    {
        $status = $this->activeTab === 'approved' ? PurchaseRequisition::STATUS_APPROVED : PurchaseRequisition::STATUS_DRAFT;

        $requisitions = $this->baseQuery()
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $vendorOptions = vendors::where('status', 'active')->orderBy('name')->get();

        return view('livewire.procurement.purchase-requisitions', [
            'requisitions' => $requisitions,
            'draftCount' => $this->draftCount(),
            'approvedCount' => $this->approvedCount(),
            'vendorOptions' => $vendorOptions,
        ]);
    }
}

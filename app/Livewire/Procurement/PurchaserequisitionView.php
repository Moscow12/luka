<?php

namespace App\Livewire\Procurement;

use App\Models\{PurchaseRequisition, PurchaseRequisitionItem, Supplier, Subdepartment, UserApproval, DocumentApprovalLevel};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PurchaserequisitionView extends Component
{
    public $purchaseRequisitionId;
    public $storeRequesting;
    public $supplier;
    public $description;
    public $items = [];
    public $suppliers = [];
    public $stores = [];
    public $canApprove = false;
    public $purchaseRequisition;

    // Disable real-time validation for better UX
    protected $validateRealTime = false;

    protected $rules = [
        'storeRequesting' => 'required',
        'supplier' => 'nullable',
        'description' => 'required|string',
        'items.*.buying_price' => 'required|numeric|min:0.01',
    ];

    protected $validationAttributes = [
        'items.*.buying_price' => 'buying price',
    ];

    public function mount($id)
    {
        // Load suppliers and stores
        $this->suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $this->stores = Subdepartment::where('active', true)->orderBy('name')->get();

        $this->purchaseRequisitionId = $id;
        $this->loadPurchaseRequisition();
        $this->checkApprovalPermission();
    }

    public function loadPurchaseRequisition()
    {
        $this->purchaseRequisition = PurchaseRequisition::with(['requisition_items.item', 'store_requesting', 'supplier'])
            ->find($this->purchaseRequisitionId);

        if ($this->purchaseRequisition) {
            $this->storeRequesting = $this->purchaseRequisition->store_requesting_id;
            $this->supplier = $this->purchaseRequisition->supplier_id;
            $this->description = $this->purchaseRequisition->requisition_description;

            // Load items
            $this->items = [];
            foreach ($this->purchaseRequisition->requisition_items as $item) {
                $this->items[] = [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->name ?? 'Unknown',
                    'item_code' => $item->item->code ?? '',
                    'unit' => $item->item->unit ?? '',
                    'units' => $item->units,
                    'itemperunit' => $item->itemperunit,
                    'quantity' => $item->quantity,
                    'buying_price' => floatval($item->buying_price ?? 0),
                    'subtotal' => floatval($item->subtotal ?? 0),
                    'remarks' => $item->remarks,
                ];
            }
        }
    }

    public function checkApprovalPermission()
    {
        $this->canApprove = UserApproval::whereHas('approval_level', function ($query) {
            $query->where('document_type', 'purchase_requisition');
        })->where('user_id', auth()->id())->exists();
    }

    public function updated($propertyName)
    {
        // Check if an item's buying price was updated
        if (preg_match('/items\.(\d+)\.buying_price/', $propertyName, $matches)) {
            $index = $matches[1];
            if (isset($this->items[$index])) {
                $price = floatval($this->items[$index]['buying_price'] ?? 0);
                $qty = floatval($this->items[$index]['quantity'] ?? 0);
                $this->items[$index]['subtotal'] = $price * $qty;
            }
        }
    }

    public function getGrandTotalProperty()
    {
        return array_sum(array_column($this->items, 'subtotal'));
    }

    public function savePurchaseRequisition()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->purchaseRequisition) {
                // Update existing PR
                $this->purchaseRequisition->update([
                    'store_requesting_id' => $this->storeRequesting,
                    'supplier_id' => $this->supplier,
                    'requisition_description' => $this->description,
                ]);

                // Update items with buying price and subtotal
                foreach ($this->items as $itemData) {
                    $item = PurchaseRequisitionItem::find($itemData['id']);
                    if ($item) {
                        $buyingPrice = floatval($itemData['buying_price'] ?? 0);
                        $quantity = floatval($itemData['quantity'] ?? 0);
                        $subtotal = $buyingPrice * $quantity;

                        $item->buying_price = $buyingPrice;
                        $item->subtotal = $subtotal;
                        $item->save();
                    }
                }
            }

            DB::commit();
            $this->dispatch('success', 'Purchase requisition saved successfully!');
            $this->loadPurchaseRequisition(); // Refresh data

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to save: ' . $th->getMessage());
        }
    }

    public function approvePurchaseRequisition()
    {
        if (!$this->purchaseRequisition) {
            return $this->dispatch('error', 'Purchase requisition not found.');
        }

        if (!$this->purchaseRequisition->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to approve at this level.');
        }

        if ($this->purchaseRequisition->status !== 'pending') {
            return $this->dispatch('error', 'Only pending purchase requisitions can be approved.');
        }

        // Validate and save buying prices before approval
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->dispatch('error', 'Cannot approve: Please ensure all items have valid buying prices (must be numeric and greater than 0).');
        }

        DB::beginTransaction();
        try {
            // Save buying prices and subtotals before approval
            foreach ($this->items as $itemData) {
                $item = PurchaseRequisitionItem::find($itemData['id']);
                if ($item) {
                    $buyingPrice = floatval($itemData['buying_price'] ?? 0);
                    $quantity = floatval($itemData['quantity'] ?? 0);
                    $subtotal = $buyingPrice * $quantity;

                    $item->buying_price = $buyingPrice;
                    $item->subtotal = $subtotal;
                    $item->save();
                }
            }
            // Record approval in history
            $history = $this->purchaseRequisition->approval_history ?? [];
            $history[] = [
                'level' => $this->purchaseRequisition->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'approved'
            ];
            $this->purchaseRequisition->approval_history = $history;

            // Check if there's a next approval level
            $nextLevel = $this->purchaseRequisition->getNextApprovalLevel();

            if ($nextLevel) {
                // Move to next approval level
                $this->purchaseRequisition->current_approval_level = $nextLevel->label;
                $this->purchaseRequisition->save();

                $approvers = $this->purchaseRequisition->getCurrentLevelApprovers();
                $approverNames = $approvers->pluck('name')->implode(', ');

                DB::commit();
                $this->dispatch('success', "Purchase requisition moved to next approval level. Pending approval from: {$approverNames}");
            } else {
                // Final approval - mark as approved and redirect to LPO
                $this->purchaseRequisition->status = 'approved';
                $this->purchaseRequisition->approved_by = auth()->id();
                $this->purchaseRequisition->save();

                DB::commit();
                $this->dispatch('success', 'Purchase requisition fully approved! Redirecting to LPO creation...');

                // Redirect to LPO page
                return redirect()->route('lpo', ['pr' => $this->purchaseRequisition->id]);
            }

            $this->loadPurchaseRequisition();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to approve: ' . $th->getMessage());
        }
    }

    public function rejectPurchaseRequisition()
    {
        if (!$this->purchaseRequisition) {
            return $this->dispatch('error', 'Purchase requisition not found.');
        }

        if (!$this->purchaseRequisition->canUserApprove(auth()->id())) {
            return $this->dispatch('error', 'You do not have permission to reject at this level.');
        }

        if ($this->purchaseRequisition->status !== 'pending') {
            return $this->dispatch('error', 'Only pending purchase requisitions can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Record rejection in history
            $history = $this->purchaseRequisition->approval_history ?? [];
            $history[] = [
                'level' => $this->purchaseRequisition->current_approval_level,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'action' => 'rejected'
            ];

            $this->purchaseRequisition->approval_history = $history;
            $this->purchaseRequisition->status = 'rejected';
            $this->purchaseRequisition->save();

            DB::commit();
            $this->dispatch('success', 'Purchase requisition rejected.');
            $this->loadPurchaseRequisition();

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed to reject: ' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.procurement.purchaserequisition-view');
    }
}

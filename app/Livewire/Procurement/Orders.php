<?php

namespace App\Livewire\Procurement;

use App\Models\departments as Department;
use App\Models\Employee;
use App\Models\productcategory as ProductCategory;
use App\Models\products as Product;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public $search = '';

    public $order_id;

    public $modalMode = 'create';

    public $showModal = false;

    // View modal
    public $showViewModal = false;

    public $viewingOrder = null;

    // Order fields
    public $order_description;

    public $status = 'draft';

    // Item management
    public $selectedItems = [];

    public $itemSearch = '';

    public $showItemSelector = false;

    public $filterCategory = '';

    // Quick-add product (when searched item doesn't exist)
    public $showAddProductForm = false;

    public $new_product_name = '';

    public $new_product_category_id = '';

    public $new_product_unit = '';

    public $new_product_code = '';

    public $new_product_description = '';

    public $new_product_reorder_level = 0;

    // Order list filters
    public $filterDepartment = '';

    public $filterStatus = '';

    public $filterDateFrom = '';

    public $filterDateTo = '';

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $order = StoreOrder::with('items.item.category')->findOrFail($id);

            if ($order->requested_by !== Auth::id() || ! $order->canEdit()) {
                session()->flash('error', 'You cannot edit this order.');
                $this->showModal = false;

                return;
            }

            $this->order_id = $id;
            $this->order_description = $order->order_description;
            $this->status = $order->status;

            $this->selectedItems = $order->items->map(function ($orderItem) {
                return [
                    'item_id' => $orderItem->item_id,
                    'category_id' => $orderItem->item->product_category_id,
                    'name' => $orderItem->item->name,
                    'category_name' => $orderItem->item->category->name ?? 'Uncategorized',
                    'unit' => $orderItem->item->unit,
                    'quantity' => $orderItem->quantity,
                    'remarks' => $orderItem->remarks,
                ];
            })->toArray();
        } else {
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset(['order_id', 'order_description', 'selectedItems']);
        $this->status = 'draft';
    }

    public function addItem($itemId, $itemName, $categoryId, $categoryName, $unit)
    {
        $exists = collect($this->selectedItems)->firstWhere('item_id', $itemId);
        if (! $exists) {
            $this->selectedItems[] = [
                'item_id' => $itemId,
                'category_id' => $categoryId,
                'name' => $itemName,
                'category_name' => $categoryName,
                'unit' => $unit,
                'quantity' => 1,
                'remarks' => '',
            ];
        }
        $this->showItemSelector = false;
        $this->itemSearch = '';
        $this->filterCategory = '';
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
    }

    public function openAddProductForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->new_product_name = $this->itemSearch;
        $this->new_product_category_id = $this->filterCategory;
        $this->new_product_unit = '';
        $this->new_product_code = '';
        $this->new_product_description = '';
        $this->new_product_reorder_level = 0;
        $this->showAddProductForm = true;
    }

    public function closeAddProductForm()
    {
        $this->showAddProductForm = false;
        $this->reset([
            'new_product_name', 'new_product_category_id', 'new_product_unit',
            'new_product_code', 'new_product_description', 'new_product_reorder_level',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeItemSelector()
    {
        $this->showItemSelector = false;
        $this->itemSearch = '';
        $this->filterCategory = '';
        $this->closeAddProductForm();
    }

    public function saveNewProduct()
    {
        $this->validate([
            'new_product_name' => ['required', 'string', 'max:255'],
            'new_product_category_id' => ['required', 'exists:productcategories,id'],
            'new_product_unit' => ['required', 'string', 'max:255'],
            'new_product_code' => ['nullable', 'string', 'max:100', 'unique:products,code'],
            'new_product_description' => ['nullable', 'string'],
            'new_product_reorder_level' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'new_product_name' => 'item name',
            'new_product_category_id' => 'category',
            'new_product_unit' => 'unit',
            'new_product_code' => 'code',
            'new_product_reorder_level' => 'reorder level',
        ]);

        $product = Product::create([
            'name' => $this->new_product_name,
            'description' => $this->new_product_description ?: null,
            'code' => $this->new_product_code ?: null,
            'unit' => $this->new_product_unit,
            'reorder_level' => $this->new_product_reorder_level ?: 0,
            'product_category_id' => $this->new_product_category_id,
            'status' => 'active',
            'track_stock' => true,
            'type' => 'good',
            'added_by' => Auth::id(),
        ]);

        $category = ProductCategory::find($product->product_category_id);

        $this->addItem($product->id, $product->name, $product->product_category_id, $category->name ?? 'Uncategorized', $product->unit);

        $this->closeAddProductForm();
        $this->itemSearch = '';
        session()->flash('success', 'New product added and included in the order.');
    }

    protected function currentDepartmentId()
    {
        return Employee::where('user_id', Auth::id())->value('department_id');
    }

    protected function canViewOrder(StoreOrder $order): bool
    {
        if (Auth::user()->isSuperAdmin()) {
            return true;
        }

        return $order->department_id === $this->currentDepartmentId();
    }

    public function save()
    {
        $this->validate([
            'order_description' => ['nullable', 'string'],
            'selectedItems' => ['required', 'array', 'min:1'],
        ]);

        $departmentId = $this->currentDepartmentId();

        if (! $departmentId) {
            session()->flash('error', 'You must be assigned to a department to raise an order.');

            return;
        }

        DB::transaction(function () use ($departmentId) {
            if ($this->modalMode === 'edit' && $this->order_id) {
                $order = StoreOrder::findOrFail($this->order_id);

                if ($order->requested_by !== Auth::id() || ! $order->canEdit()) {
                    throw new \Exception('Unauthorized');
                }

                $order->update([
                    'order_description' => $this->order_description,
                ]);

                $order->items()->delete();
            } else {
                $order = StoreOrder::create([
                    'order_number' => StoreOrder::generateOrderNumber(),
                    'department_id' => $departmentId,
                    'status' => 'draft',
                    'order_description' => $this->order_description,
                    'requested_by' => Auth::id(),
                ]);
            }

            foreach ($this->selectedItems as $item) {
                StoreOrderItem::create([
                    'store_order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'remarks' => $item['remarks'],
                ]);
            }
        });

        session()->flash('success', $this->modalMode === 'edit' ? 'Order updated successfully!' : 'Order raised successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    public function submit($id)
    {
        $order = StoreOrder::findOrFail($id);

        if ($order->requested_by !== Auth::id() || ! $order->canSubmit()) {
            session()->flash('error', 'Cannot submit this order.');

            return;
        }

        $order->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        session()->flash('success', 'Order submitted successfully!');
    }

    public function delete($id)
    {
        $order = StoreOrder::findOrFail($id);

        if ($order->requested_by !== Auth::id() || ! $order->isDraft()) {
            session()->flash('error', 'Cannot delete this order.');

            return;
        }

        $order->delete();
        session()->flash('success', 'Order deleted successfully!');
    }

    public function openViewModal($id)
    {
        $this->viewingOrder = StoreOrder::with([
            'items.item.category',
            'items.purchaseRequisitionItems.purchaseRequisition',
            'items.purchaseRequisitionItems.localPurchaseOrderItem.localPurchaseOrder',
            'department',
            'requestedBy',
            'approvedBy',
            'assignedDuties.employee',
        ])->findOrFail($id);

        if (! $this->canViewOrder($this->viewingOrder)) {
            session()->flash('error', 'You cannot view this order.');
            $this->viewingOrder = null;

            return;
        }

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingOrder = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
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

    public function resetFilters()
    {
        $this->reset(['search', 'filterDepartment', 'filterStatus', 'filterDateFrom', 'filterDateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();
        $departmentId = $this->currentDepartmentId();

        $orders = StoreOrder::query()
            ->with(['department', 'items', 'requestedBy'])
            ->when(! $isSuperAdmin, fn ($q) => $q->where('department_id', $departmentId))
            ->when($isSuperAdmin && $this->filterDepartment, fn ($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('order_description', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $departments = $isSuperAdmin ? Department::orderBy('name')->get() : collect();

        $categories = ProductCategory::orderBy('name')->get();

        $availableItems = Product::query()
            ->with('category')
            ->where('status', 'active')
            ->where('track_stock', true)
            ->when($this->itemSearch, function ($query) {
                $query->where('name', 'like', '%'.$this->itemSearch.'%');
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('product_category_id', $this->filterCategory);
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.procurement.orders', [
            'orders' => $orders,
            'categories' => $categories,
            'availableItems' => $availableItems,
            'departments' => $departments,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}

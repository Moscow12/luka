<?php

namespace App\Livewire\Chop;

use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Models\chopcategoryarea;
use App\Models\chopitems;
use App\Models\departments;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentBudgetRequest extends Component
{
    use WithPagination;

    public $search = '';

    public $request_id;

    public $modalMode = 'create';

    public $showModal = false;

    // View modal
    public $showViewModal = false;

    public $viewingRequest = null;

    // Request fields
    public $financial_year_id;

    public $justification;

    public $status = 'draft';

    // Item management
    public $selectedItems = [];

    public $itemSearch = '';

    public $showItemSelector = false;

    public $filterCategory = '';

    public function mount()
    {
        // Set default to current financial year
        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->financial_year_id = $currentFY->id;
        }
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $request = BudgetRequest::with('items.item', 'items.category')->findOrFail($id);

            if ($request->requested_by !== Auth::id() || ! $request->canEdit()) {
                session()->flash('error', 'You cannot edit this request.');
                $this->showModal = false;

                return;
            }

            $this->request_id = $id;
            $this->financial_year_id = $request->financial_year_id;
            $this->justification = $request->justification;
            $this->status = $request->status;

            $this->selectedItems = $request->items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'category_id' => $item->category_id,
                    'name' => $item->item->name,
                    'category_name' => $item->category->name,
                    'quantity' => $item->requested_quantity,
                    'price' => $item->requested_price,
                    'justification' => $item->justification,
                ];
            })->toArray();
        } else {
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset(['request_id', 'justification', 'selectedItems']);
        $this->status = 'draft';

        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->financial_year_id = $currentFY->id;
        }
    }

    public function addItem($itemId, $itemName, $categoryId, $categoryName)
    {
        $exists = collect($this->selectedItems)->firstWhere('item_id', $itemId);
        if (! $exists) {
            $this->selectedItems[] = [
                'item_id' => $itemId,
                'category_id' => $categoryId,
                'name' => $itemName,
                'category_name' => $categoryName,
                'quantity' => 1,
                'price' => 0,
                'justification' => '',
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

    public function save()
    {
        $this->validate([
            'financial_year_id' => ['required', 'exists:financial_years,id'],
            'justification' => ['nullable', 'string'],
            'selectedItems' => ['required', 'array', 'min:1'],
        ]);

        $user = Auth::user();
        $departmentId = $user->department_id ?? departments::first()?->id;

        if (! $departmentId) {
            session()->flash('error', 'You must be assigned to a department to create budget requests.');

            return;
        }

        DB::transaction(function () use ($departmentId) {
            $totalAmount = collect($this->selectedItems)->sum(function ($item) {
                return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
            });

            if ($this->modalMode === 'edit' && $this->request_id) {
                $request = BudgetRequest::findOrFail($this->request_id);

                if ($request->requested_by !== Auth::id() || ! $request->canEdit()) {
                    throw new \Exception('Unauthorized');
                }

                $request->update([
                    'financial_year_id' => $this->financial_year_id,
                    'justification' => $this->justification,
                    'total_estimated_amount' => $totalAmount,
                ]);

                $request->items()->delete();
            } else {
                $request = BudgetRequest::create([
                    'request_number' => BudgetRequest::generateRequestNumber($this->financial_year_id),
                    'department_id' => $departmentId,
                    'financial_year_id' => $this->financial_year_id,
                    'status' => 'draft',
                    'total_estimated_amount' => $totalAmount,
                    'justification' => $this->justification,
                    'requested_by' => Auth::id(),
                ]);
            }

            foreach ($this->selectedItems as $item) {
                BudgetRequestItem::create([
                    'budget_request_id' => $request->id,
                    'item_id' => $item['item_id'],
                    'category_id' => $item['category_id'],
                    'requested_quantity' => $item['quantity'],
                    'requested_price' => $item['price'],
                    'justification' => $item['justification'],
                ]);
            }
        });

        session()->flash('success', $this->modalMode === 'edit' ? 'Request updated successfully!' : 'Request created successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    public function submit($id)
    {
        $request = BudgetRequest::findOrFail($id);

        if ($request->requested_by !== Auth::id() || ! $request->canSubmit()) {
            session()->flash('error', 'Cannot submit this request.');

            return;
        }

        $request->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        session()->flash('success', 'Request submitted successfully!');
    }

    public function delete($id)
    {
        $request = BudgetRequest::findOrFail($id);

        if ($request->requested_by !== Auth::id() || ! $request->isDraft()) {
            session()->flash('error', 'Cannot delete this request.');

            return;
        }

        $request->delete();
        session()->flash('success', 'Request deleted successfully!');
    }

    public function openViewModal($id)
    {
        $this->viewingRequest = BudgetRequest::with([
            'items.item',
            'items.category',
            'department',
            'financialYear',
            'requestedBy',
            'reviewedBy',
        ])->findOrFail($id);

        // Verify ownership
        if ($this->viewingRequest->requested_by !== Auth::id()) {
            session()->flash('error', 'You cannot view this request.');

            return;
        }

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingRequest = null;
    }

    public function printRequest($id)
    {
        // Just open the view modal, user can click print button there
        $this->openViewModal($id);
    }

    public function render()
    {
        $user = Auth::user();
        $departmentId = $user->department_id ?? null;

        $requests = BudgetRequest::query()
            ->with(['financialYear', 'department', 'items'])
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->where('requested_by', Auth::id())
            ->where(function ($query) {
                $query->whereHas('financialYear', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                })->orWhere('request_number', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();
        $categories = chopcategoryarea::orderBy('name')->get();

        $availableItems = chopitems::query()
            ->with('category')
            ->where('is_active', true)
            ->when($this->itemSearch, function ($query) {
                $query->where('name', 'like', '%'.$this->itemSearch.'%');
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.chop.department-budget-request', [
            'requests' => $requests,
            'financialYears' => $financialYears,
            'categories' => $categories,
            'availableItems' => $availableItems,
        ]);
    }
}

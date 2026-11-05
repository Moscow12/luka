<?php

namespace App\Livewire\Performance\DepartmentPlans;

use App\Models\DepartmentPlanItem;
use App\Services\Performance\DepartmentPlanAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManagePlanItems extends Component
{
    #[Locked]
    public $planId;

    public $plan;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    #[Locked]
    public $editingItemId = null;

    // Item Form Fields
    public $itemForm = [
        'department_plan_id' => '',
        'item_name' => '',
        'description' => '',
        'weight' => 0,
        'target_value' => '',
        'target_unit' => '',
        'min_acceptable' => '',
        'max_possible' => '',
        'department_specific_notes' => '',
        'display_order' => 0,
        'is_active' => true,
    ];

    public function mount($planId)
    {
        $this->planId = $planId;
        $this->plan = DepartmentPlanAccess::visiblePlans(Auth::user())
            ->with(['department', 'organizationalPlan'])
            ->findOrFail($planId);
    }

    private function authorizeManage(): void
    {
        abort_unless(DepartmentPlanAccess::canManage(Auth::user()), 403);
    }

    /** Items of this plan only: item ids arrive from the client. */
    private function planItems(): Builder
    {
        return DepartmentPlanItem::where('department_plan_id', $this->planId);
    }

    /** Plans an item can be added to: those this user can see, while still open. */
    private function selectablePlans(): Builder
    {
        return DepartmentPlanAccess::visiblePlans(Auth::user())->whereIn('status', ['draft', 'active']);
    }

    private function nextDisplayOrder($planId): int
    {
        return DepartmentPlanItem::where('department_plan_id', $planId)->max('display_order') + 1;
    }

    public function createItem()
    {
        $this->authorizeManage();

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingItemId = null;
        $this->itemForm = [
            'department_plan_id' => $this->planId,
            'item_name' => '',
            'description' => '',
            'weight' => 0,
            'target_value' => '',
            'target_unit' => '',
            'min_acceptable' => '',
            'max_possible' => '',
            'department_specific_notes' => '',
            'display_order' => $this->nextDisplayOrder($this->planId),
            'is_active' => true,
        ];
        $this->showModal = true;
    }

    public function editItem($itemId)
    {
        $this->authorizeManage();

        $item = $this->planItems()->findOrFail($itemId);

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingItemId = $itemId;

        $this->itemForm = [
            'department_plan_id' => $item->department_plan_id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'weight' => $item->weight,
            'target_value' => $item->target_value,
            'target_unit' => $item->target_unit,
            'min_acceptable' => $item->min_acceptable,
            'max_possible' => $item->max_possible,
            'department_specific_notes' => $item->department_specific_notes,
            'display_order' => $item->display_order,
            'is_active' => $item->is_active,
        ];
        $this->showModal = true;
    }

    public function updatedItemFormDepartmentPlanId($value)
    {
        // Numbering continues within whichever plan the item is going to
        $this->itemForm['display_order'] = $this->nextDisplayOrder($value);
    }

    public function saveItem()
    {
        $this->authorizeManage();

        $rules = [
            'itemForm.department_plan_id' => 'required',
            'itemForm.item_name' => 'required|string|max:255',
            'itemForm.description' => 'nullable|string',
            'itemForm.weight' => 'required|numeric|min:0|max:100',
            'itemForm.target_value' => 'nullable|numeric',
            'itemForm.target_unit' => 'nullable|string|max:50',
            'itemForm.min_acceptable' => 'nullable|numeric',
            'itemForm.max_possible' => 'nullable|numeric',
            'itemForm.department_specific_notes' => 'nullable|string',
            'itemForm.display_order' => 'required|integer|min:0',
            'itemForm.is_active' => 'boolean',
        ];

        $messages = [
            'itemForm.department_plan_id.required' => 'Please select a department plan.',
            'itemForm.item_name.required' => 'Please enter the item name.',
            'itemForm.weight.required' => 'Please enter the weight.',
            'itemForm.weight.max' => 'Weight cannot exceed 100%.',
        ];

        $this->validate($rules, $messages);

        $editingItem = $this->modalMode === 'edit' && $this->editingItemId
            ? $this->planItems()->findOrFail($this->editingItemId)
            : null;

        // A new item goes to the chosen plan, which comes from the client. An edited item stays put.
        $targetPlan = $editingItem ? null : $this->selectablePlans()->find($this->itemForm['department_plan_id']);

        if (! $editingItem && ! $targetPlan) {
            $this->addError('itemForm.department_plan_id', 'The selected department plan is not available.');

            return;
        }

        try {
            DB::beginTransaction();

            $data = [
                'item_name' => $this->itemForm['item_name'],
                'description' => $this->itemForm['description'],
                'weight' => $this->itemForm['weight'],
                'target_value' => $this->itemForm['target_value'] ?: null,
                'target_unit' => $this->itemForm['target_unit'],
                'min_acceptable' => $this->itemForm['min_acceptable'] ?: null,
                'max_possible' => $this->itemForm['max_possible'] ?: null,
                'department_specific_notes' => $this->itemForm['department_specific_notes'],
                'display_order' => $this->itemForm['display_order'],
                'is_active' => $this->itemForm['is_active'],
            ];

            if ($editingItem) {
                $editingItem->update($data);
                session()->flash('success', 'Department plan item updated successfully!');
            } else {
                DepartmentPlanItem::create($data + ['department_plan_id' => $targetPlan->id]);
                session()->flash('success', "Item added to {$targetPlan->plan_name} successfully!");
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingItemId = null;
        $this->resetErrorBag();
    }

    public function deleteItem($itemId)
    {
        $this->authorizeManage();

        $item = $this->planItems()->withCount('employeePlanItems')->findOrFail($itemId);

        try {
            // Check if item has been distributed to employee plans
            if ($item->employee_plan_items_count > 0) {
                session()->flash('error', 'Cannot delete this item because it has been distributed to '.$item->employee_plan_items_count.' employee plan(s).');

                return;
            }

            DB::beginTransaction();
            $item->delete();
            DB::commit();

            session()->flash('success', 'Department plan item deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function toggleActive($itemId)
    {
        $this->authorizeManage();

        $item = $this->planItems()->findOrFail($itemId);

        try {
            $item->update(['is_active' => ! $item->is_active]);
            session()->flash('success', 'Item status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        $items = DepartmentPlanItem::where('department_plan_id', $this->planId)
            ->with('organizationalPlanItem')
            ->withCount('employeePlanItems')
            ->orderBy('display_order')
            ->get();

        $totalWeight = $items->sum('weight');

        return view('livewire.performance.department-plans.manage-plan-items', [
            'items' => $items,
            'totalWeight' => $totalWeight,
            'canManage' => DepartmentPlanAccess::canManage(Auth::user()),
            'departmentPlans' => $this->showModal && ! $this->editingItemId
                ? $this->selectablePlans()->with('department')->orderBy('plan_name')->get()
                : collect(),
        ]);
    }
}

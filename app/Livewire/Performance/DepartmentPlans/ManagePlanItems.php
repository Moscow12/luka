<?php

namespace App\Livewire\Performance\DepartmentPlans;

use App\Models\DepartmentPlan;
use App\Models\DepartmentPlanItem;
use App\Models\OrganizationalPlanItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManagePlanItems extends Component
{
    public $planId;

    public $plan;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    public $editingItemId = null;

    // Item Form Fields
    public $itemForm = [
        'organizational_plan_item_id' => '',
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
        $this->plan = DepartmentPlan::with(['department', 'organizationalPlan'])->findOrFail($planId);
    }

    public function createItem()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingItemId = null;
        $this->itemForm = [
            'organizational_plan_item_id' => '',
            'item_name' => '',
            'description' => '',
            'weight' => 0,
            'target_value' => '',
            'target_unit' => '',
            'min_acceptable' => '',
            'max_possible' => '',
            'department_specific_notes' => '',
            'display_order' => DepartmentPlanItem::where('department_plan_id', $this->planId)->max('display_order') + 1,
            'is_active' => true,
        ];
        $this->showModal = true;
    }

    public function editItem($itemId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingItemId = $itemId;

        $item = DepartmentPlanItem::findOrFail($itemId);
        $this->itemForm = [
            'organizational_plan_item_id' => $item->organizational_plan_item_id,
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

    public function updatedItemFormOrganizationalPlanItemId($value)
    {
        if ($value) {
            $orgItem = OrganizationalPlanItem::find($value);
            if ($orgItem) {
                $this->itemForm['item_name'] = $orgItem->item_name;
                $this->itemForm['description'] = $orgItem->description;
                $this->itemForm['weight'] = $orgItem->weight;
                $this->itemForm['target_value'] = $orgItem->target_value;
                $this->itemForm['target_unit'] = $orgItem->target_unit;
                $this->itemForm['min_acceptable'] = $orgItem->min_acceptable;
                $this->itemForm['max_possible'] = $orgItem->max_possible;
            }
        }
    }

    public function saveItem()
    {
        $rules = [
            'itemForm.organizational_plan_item_id' => 'required|exists:organizational_plan_items,id',
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
            'itemForm.organizational_plan_item_id.required' => 'Please select an organizational plan item.',
            'itemForm.item_name.required' => 'Please enter the item name.',
            'itemForm.weight.required' => 'Please enter the weight.',
            'itemForm.weight.max' => 'Weight cannot exceed 100%.',
        ];

        $this->validate($rules, $messages);

        // Check for duplicate org plan item in this department plan
        $existingItem = DepartmentPlanItem::where('department_plan_id', $this->planId)
            ->where('organizational_plan_item_id', $this->itemForm['organizational_plan_item_id'])
            ->when($this->editingItemId, fn ($q) => $q->where('id', '!=', $this->editingItemId))
            ->first();

        if ($existingItem) {
            session()->flash('error', 'This organizational plan item has already been added to this department plan.');

            return;
        }

        try {
            DB::beginTransaction();

            $data = [
                'department_plan_id' => $this->planId,
                'organizational_plan_item_id' => $this->itemForm['organizational_plan_item_id'],
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

            if ($this->modalMode === 'edit' && $this->editingItemId) {
                $item = DepartmentPlanItem::findOrFail($this->editingItemId);
                $item->update($data);
                session()->flash('success', 'Department plan item updated successfully!');
            } else {
                DepartmentPlanItem::create($data);
                session()->flash('success', 'Department plan item created successfully!');
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
        try {
            $item = DepartmentPlanItem::withCount('employeePlanItems')->findOrFail($itemId);

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
        try {
            $item = DepartmentPlanItem::findOrFail($itemId);
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

        // Get available organizational plan items (from linked org plan)
        $availableOrgItems = [];
        if ($this->plan->organizationalPlan) {
            $usedOrgItemIds = $items->pluck('organizational_plan_item_id')->toArray();
            $availableOrgItems = OrganizationalPlanItem::where('organizational_plan_id', $this->plan->organizational_plan_id)
                ->where('is_active', true)
                ->when($this->editingItemId, function ($q) use ($usedOrgItemIds) {
                    // When editing, exclude only other items' org items
                    $currentItem = DepartmentPlanItem::find($this->editingItemId);
                    if ($currentItem) {
                        $usedOrgItemIds = array_diff($usedOrgItemIds, [$currentItem->organizational_plan_item_id]);
                    }

                    return $q->whereNotIn('id', $usedOrgItemIds);
                }, function ($q) use ($usedOrgItemIds) {
                    return $q->whereNotIn('id', $usedOrgItemIds);
                })
                ->orderBy('display_order')
                ->get();
        }

        return view('livewire.performance.department-plans.manage-plan-items', [
            'items' => $items,
            'totalWeight' => $totalWeight,
            'availableOrgItems' => $availableOrgItems,
        ]);
    }
}

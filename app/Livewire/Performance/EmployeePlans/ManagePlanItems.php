<?php

namespace App\Livewire\Performance\EmployeePlans;

use App\Models\DepartmentPlanItem;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanItem;
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
        'department_plan_item_id' => '',
        'item_name' => '',
        'description' => '',
        'weight' => 0,
        'target_value' => '',
        'target_unit' => '',
        'min_acceptable' => '',
        'max_possible' => '',
        'actual_achievement' => '',
        'score' => '',
        'achievement_notes' => '',
        'employee_comments' => '',
        'supervisor_comments' => '',
        'display_order' => 0,
        'is_active' => true,
    ];

    public function mount($planId)
    {
        $this->planId = $planId;
        $this->plan = EmployeePlan::with(['employee', 'departmentPlan.department'])->findOrFail($planId);
    }

    public function createItem()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingItemId = null;
        $this->itemForm = [
            'department_plan_item_id' => '',
            'item_name' => '',
            'description' => '',
            'weight' => 0,
            'target_value' => '',
            'target_unit' => '',
            'min_acceptable' => '',
            'max_possible' => '',
            'actual_achievement' => '',
            'score' => '',
            'achievement_notes' => '',
            'employee_comments' => '',
            'supervisor_comments' => '',
            'display_order' => EmployeePlanItem::where('employee_plan_id', $this->planId)->max('display_order') + 1,
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

        $item = EmployeePlanItem::findOrFail($itemId);
        $this->itemForm = [
            'department_plan_item_id' => $item->department_plan_item_id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'weight' => $item->weight,
            'target_value' => $item->target_value,
            'target_unit' => $item->target_unit,
            'min_acceptable' => $item->min_acceptable,
            'max_possible' => $item->max_possible,
            'actual_achievement' => $item->actual_achievement,
            'score' => $item->score,
            'achievement_notes' => $item->achievement_notes,
            'employee_comments' => $item->employee_comments,
            'supervisor_comments' => $item->supervisor_comments,
            'display_order' => $item->display_order,
            'is_active' => $item->is_active,
        ];
        $this->showModal = true;
    }

    public function updatedItemFormDepartmentPlanItemId($value)
    {
        if ($value) {
            $deptItem = DepartmentPlanItem::find($value);
            if ($deptItem) {
                $this->itemForm['item_name'] = $deptItem->item_name;
                $this->itemForm['description'] = $deptItem->description;
                $this->itemForm['weight'] = $deptItem->weight;
                $this->itemForm['target_value'] = $deptItem->target_value;
                $this->itemForm['target_unit'] = $deptItem->target_unit;
                $this->itemForm['min_acceptable'] = $deptItem->min_acceptable;
                $this->itemForm['max_possible'] = $deptItem->max_possible;
            }
        }
    }

    public function saveItem()
    {
        $rules = [
            'itemForm.department_plan_item_id' => 'nullable|exists:department_plan_items,id',
            'itemForm.item_name' => 'required|string|max:255',
            'itemForm.description' => 'nullable|string',
            'itemForm.weight' => 'required|numeric|min:0|max:100',
            'itemForm.target_value' => 'nullable|numeric',
            'itemForm.target_unit' => 'nullable|string|max:50',
            'itemForm.min_acceptable' => 'nullable|numeric',
            'itemForm.max_possible' => 'nullable|numeric',
            'itemForm.actual_achievement' => 'nullable|numeric',
            'itemForm.score' => 'nullable|numeric|min:0|max:100',
            'itemForm.achievement_notes' => 'nullable|string',
            'itemForm.employee_comments' => 'nullable|string',
            'itemForm.supervisor_comments' => 'nullable|string',
            'itemForm.display_order' => 'required|integer|min:0',
            'itemForm.is_active' => 'boolean',
        ];

        $messages = [
            'itemForm.item_name.required' => 'Please enter the item name.',
            'itemForm.weight.required' => 'Please enter the weight.',
            'itemForm.weight.max' => 'Weight cannot exceed 100%.',
            'itemForm.score.max' => 'Score cannot exceed 100.',
        ];

        $this->validate($rules, $messages);

        // Check for duplicate dept plan item in this employee plan (if linked)
        if ($this->itemForm['department_plan_item_id']) {
            $existingItem = EmployeePlanItem::where('employee_plan_id', $this->planId)
                ->where('department_plan_item_id', $this->itemForm['department_plan_item_id'])
                ->when($this->editingItemId, fn ($q) => $q->where('id', '!=', $this->editingItemId))
                ->first();

            if ($existingItem) {
                session()->flash('error', 'This department plan item has already been added to this employee plan.');

                return;
            }
        }

        try {
            DB::beginTransaction();

            $data = [
                'employee_plan_id' => $this->planId,
                'department_plan_item_id' => $this->itemForm['department_plan_item_id'] ?: null,
                'item_name' => $this->itemForm['item_name'],
                'description' => $this->itemForm['description'],
                'weight' => $this->itemForm['weight'],
                'target_value' => $this->itemForm['target_value'] ?: null,
                'target_unit' => $this->itemForm['target_unit'],
                'min_acceptable' => $this->itemForm['min_acceptable'] ?: null,
                'max_possible' => $this->itemForm['max_possible'] ?: null,
                'actual_achievement' => $this->itemForm['actual_achievement'] ?: null,
                'score' => $this->itemForm['score'] ?: null,
                'achievement_notes' => $this->itemForm['achievement_notes'],
                'employee_comments' => $this->itemForm['employee_comments'],
                'supervisor_comments' => $this->itemForm['supervisor_comments'],
                'display_order' => $this->itemForm['display_order'],
                'is_active' => $this->itemForm['is_active'],
            ];

            if ($this->modalMode === 'edit' && $this->editingItemId) {
                $item = EmployeePlanItem::findOrFail($this->editingItemId);
                $item->update($data);
                session()->flash('success', 'Employee plan item updated successfully!');
            } else {
                EmployeePlanItem::create($data);
                session()->flash('success', 'Employee plan item created successfully!');
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
            $item = EmployeePlanItem::findOrFail($itemId);

            // Check if item has been scored
            if ($item->score !== null) {
                session()->flash('error', 'Cannot delete an item that has been scored. Please clear the score first.');

                return;
            }

            DB::beginTransaction();
            $item->delete();
            DB::commit();

            session()->flash('success', 'Employee plan item deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function toggleActive($itemId)
    {
        try {
            $item = EmployeePlanItem::findOrFail($itemId);
            $item->update(['is_active' => ! $item->is_active]);
            session()->flash('success', 'Item status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        $items = EmployeePlanItem::where('employee_plan_id', $this->planId)
            ->with('departmentPlanItem')
            ->orderBy('display_order')
            ->get();

        $totalWeight = $items->sum('weight');
        $averageScore = $items->whereNotNull('score')->avg('score');
        $weightedScore = $items->sum(function ($item) {
            return ($item->score ?? 0) * ($item->weight / 100);
        });

        // Get available department plan items (from linked dept plan)
        $availableDeptItems = [];
        if ($this->plan->departmentPlan) {
            $usedDeptItemIds = $items->pluck('department_plan_item_id')->filter()->toArray();
            $availableDeptItems = DepartmentPlanItem::where('department_plan_id', $this->plan->department_plan_id)
                ->where('is_active', true)
                ->when($this->editingItemId, function ($q) use ($usedDeptItemIds) {
                    $currentItem = EmployeePlanItem::find($this->editingItemId);
                    if ($currentItem && $currentItem->department_plan_item_id) {
                        $usedDeptItemIds = array_diff($usedDeptItemIds, [$currentItem->department_plan_item_id]);
                    }

                    return $q->whereNotIn('id', $usedDeptItemIds);
                }, function ($q) use ($usedDeptItemIds) {
                    return $q->whereNotIn('id', $usedDeptItemIds);
                })
                ->orderBy('display_order')
                ->get();
        }

        return view('livewire.performance.employee-plans.manage-plan-items', [
            'items' => $items,
            'totalWeight' => $totalWeight,
            'averageScore' => $averageScore,
            'weightedScore' => $weightedScore,
            'availableDeptItems' => $availableDeptItems,
        ]);
    }
}

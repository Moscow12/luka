<?php

namespace App\Livewire\Performance\OrganizationalPlans;

use App\Models\OrganizationalPlan;
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
        'item_name' => '',
        'description' => '',
        'kpi_type' => 'quantitative',
        'measurement_type' => 'numeric',
        'weight' => 0,
        'target_value' => '',
        'target_unit' => '',
        'min_acceptable' => '',
        'max_possible' => '',
        'rating_scale_max' => '',
        'scoring_criteria' => '',
        'display_order' => 0,
        'is_active' => true,
    ];

    public function mount($planId)
    {
        $this->planId = $planId;
        $this->plan = OrganizationalPlan::findOrFail($planId);
    }

    public function createItem()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingItemId = null;
        $this->itemForm = [
            'item_name' => '',
            'description' => '',
            'kpi_type' => 'quantitative',
            'measurement_type' => 'numeric',
            'weight' => 0,
            'target_value' => '',
            'target_unit' => '',
            'min_acceptable' => '',
            'max_possible' => '',
            'rating_scale_max' => '',
            'scoring_criteria' => '',
            'display_order' => OrganizationalPlanItem::where('organizational_plan_id', $this->planId)->max('display_order') + 1,
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

        $item = OrganizationalPlanItem::findOrFail($itemId);
        $this->itemForm = [
            'item_name' => $item->item_name,
            'description' => $item->description,
            'kpi_type' => $item->kpi_type,
            'measurement_type' => $item->measurement_type,
            'weight' => $item->weight,
            'target_value' => $item->target_value,
            'target_unit' => $item->target_unit,
            'min_acceptable' => $item->min_acceptable,
            'max_possible' => $item->max_possible,
            'rating_scale_max' => $item->rating_scale_max,
            'scoring_criteria' => $item->scoring_criteria,
            'display_order' => $item->display_order,
            'is_active' => $item->is_active,
        ];
        $this->showModal = true;
    }

    public function saveItem()
    {
        $rules = [
            'itemForm.item_name' => 'required|string|max:255',
            'itemForm.description' => 'nullable|string',
            'itemForm.kpi_type' => 'required|in:qualitative,quantitative',
            'itemForm.measurement_type' => 'required|in:numeric,boolean,percentage,rating_scale',
            'itemForm.weight' => 'required|numeric|min:0|max:100',
            'itemForm.target_value' => 'nullable|numeric',
            'itemForm.target_unit' => 'nullable|string|max:50',
            'itemForm.min_acceptable' => 'nullable|numeric',
            'itemForm.max_possible' => 'nullable|numeric',
            'itemForm.rating_scale_max' => 'nullable|integer|min:1|max:10',
            'itemForm.scoring_criteria' => 'nullable|string',
            'itemForm.display_order' => 'required|integer|min:0',
            'itemForm.is_active' => 'boolean',
        ];

        $messages = [
            'itemForm.item_name.required' => 'Please enter the item name.',
            'itemForm.kpi_type.required' => 'Please select a KPI type.',
            'itemForm.measurement_type.required' => 'Please select a measurement type.',
            'itemForm.weight.required' => 'Please enter the weight.',
            'itemForm.weight.max' => 'Weight cannot exceed 100%.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $data = [
                'organizational_plan_id' => $this->planId,
                'item_name' => $this->itemForm['item_name'],
                'description' => $this->itemForm['description'],
                'kpi_type' => $this->itemForm['kpi_type'],
                'measurement_type' => $this->itemForm['measurement_type'],
                'weight' => $this->itemForm['weight'],
                'target_value' => $this->itemForm['target_value'] ?: null,
                'target_unit' => $this->itemForm['target_unit'],
                'min_acceptable' => $this->itemForm['min_acceptable'] ?: null,
                'max_possible' => $this->itemForm['max_possible'] ?: null,
                'rating_scale_max' => $this->itemForm['rating_scale_max'] ?: null,
                'scoring_criteria' => $this->itemForm['scoring_criteria'],
                'display_order' => $this->itemForm['display_order'],
                'is_active' => $this->itemForm['is_active'],
            ];

            if ($this->modalMode === 'edit' && $this->editingItemId) {
                $item = OrganizationalPlanItem::findOrFail($this->editingItemId);
                $item->update($data);
                session()->flash('success', 'Plan item updated successfully!');
            } else {
                OrganizationalPlanItem::create($data);
                session()->flash('success', 'Plan item created successfully!');
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
            $item = OrganizationalPlanItem::withCount('departmentPlanItems')->findOrFail($itemId);

            // Check if item has been distributed to department plans
            if ($item->department_plan_items_count > 0) {
                session()->flash('error', 'Cannot delete this item because it has been distributed to '.$item->department_plan_items_count.' department plan(s).');

                return;
            }

            DB::beginTransaction();
            $item->delete();
            DB::commit();

            session()->flash('success', 'Plan item deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function toggleActive($itemId)
    {
        try {
            $item = OrganizationalPlanItem::findOrFail($itemId);
            $item->update(['is_active' => ! $item->is_active]);
            session()->flash('success', 'Item status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        $items = OrganizationalPlanItem::where('organizational_plan_id', $this->planId)
            ->withCount('departmentPlanItems')
            ->orderBy('display_order')
            ->get();

        $totalWeight = $items->sum('weight');

        return view('livewire.performance.organizational-plans.manage-plan-items', [
            'items' => $items,
            'totalWeight' => $totalWeight,
        ]);
    }
}

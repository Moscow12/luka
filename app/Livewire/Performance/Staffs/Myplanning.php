<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\DepartmentPlan;
use App\Models\DepartmentPlanItem;
use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Myplanning extends Component
{
    public $employee;
    public $hasEmployeeRecord = false;
    public $departmentPlans = [];
    public $departmentPlanItems = [];
    public $myPlans = [];
    public $selectedDepartmentPlan = null;

    // Modal states
    public $showPlanModal = false;
    public $showItemModal = false;
    public $modalMode = 'create';

    // Plan form fields
    public $planId;
    public $department_plan_id;
    public $plan_name;
    public $plan_description;
    public $plan_status = 'draft';

    // Item form fields
    public $itemId;
    public $employee_plan_id;
    public $department_plan_item_id;
    public $item_name;
    public $item_description;
    public $weight = 0;
    public $target_value;
    public $target_unit;
    public $min_acceptable;
    public $max_possible;

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->loadDepartmentPlans();
            $this->loadMyPlans();
        }
    }

    public function loadDepartmentPlans()
    {
        if (!$this->employee || !$this->employee->department_id) return;

        $this->departmentPlans = DepartmentPlan::where('department_id', $this->employee->department_id)
            ->where('status', 'active')
            ->with('departmentPlanItems')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadMyPlans()
    {
        if (!$this->employee) return;

        $this->myPlans = EmployeePlan::where('employee_id', $this->employee->id)
            ->with(['employeePlanItems.departmentPlanItem', 'departmentPlan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectDepartmentPlan($planId)
    {
        $this->selectedDepartmentPlan = DepartmentPlan::with('departmentPlanItems')->find($planId);
        $this->departmentPlanItems = $this->selectedDepartmentPlan?->departmentPlanItems ?? [];
    }

    // Plan Modal Methods
    public function openPlanModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->modalMode = $mode;
        $this->showPlanModal = true;

        if ($mode === 'edit' && $id) {
            $plan = EmployeePlan::findOrFail($id);
            $this->planId = $plan->id;
            $this->department_plan_id = $plan->department_plan_id;
            $this->plan_name = $plan->plan_name;
            $this->plan_description = $plan->description;
            $this->plan_status = $plan->status;
        } else {
            $this->resetPlanForm();
        }
    }

    public function resetPlanForm()
    {
        $this->reset(['planId', 'department_plan_id', 'plan_name', 'plan_description']);
        $this->plan_status = 'draft';
    }

    public function savePlan()
    {
        $this->validate([
            'department_plan_id' => ['required', 'exists:department_plans,id'],
            'plan_name' => ['required', 'string', 'max:255'],
            'plan_description' => ['nullable', 'string'],
        ]);

        $data = [
            'department_plan_id' => $this->department_plan_id,
            'employee_id' => $this->employee->id,
            'plan_name' => $this->plan_name,
            'description' => $this->plan_description,
            'status' => $this->plan_status,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ];

        if ($this->modalMode === 'edit' && $this->planId) {
            $plan = EmployeePlan::where('id', $this->planId)
                ->where('employee_id', $this->employee->id)
                ->firstOrFail();
            $plan->update($data);
            session()->flash('success', 'Plan updated successfully!');
        } else {
            EmployeePlan::create($data);
            session()->flash('success', 'Plan created successfully!');
        }

        $this->showPlanModal = false;
        $this->resetPlanForm();
        $this->loadMyPlans();
    }

    public function deletePlan($id)
    {
        $plan = EmployeePlan::where('id', $id)
            ->where('employee_id', $this->employee->id)
            ->firstOrFail();
        $plan->delete();
        session()->flash('success', 'Plan deleted successfully!');
        $this->loadMyPlans();
    }

    // Item Modal Methods
    public function openItemModal($mode = 'create', $planId = null, $itemId = null)
    {
        $this->resetErrorBag();
        $this->modalMode = $mode;
        $this->showItemModal = true;
        $this->employee_plan_id = $planId;

        if ($mode === 'edit' && $itemId) {
            $item = EmployeePlanItem::findOrFail($itemId);
            $this->itemId = $item->id;
            $this->employee_plan_id = $item->employee_plan_id;
            $this->department_plan_item_id = $item->department_plan_item_id;
            $this->item_name = $item->item_name;
            $this->item_description = $item->description;
            $this->weight = $item->weight;
            $this->target_value = $item->target_value;
            $this->target_unit = $item->target_unit;
            $this->min_acceptable = $item->min_acceptable;
            $this->max_possible = $item->max_possible;
        } else {
            $this->resetItemForm();
            $this->employee_plan_id = $planId;
        }
    }

    public function resetItemForm()
    {
        $this->reset([
            'itemId', 'department_plan_item_id', 'item_name', 'item_description',
            'target_value', 'target_unit', 'min_acceptable', 'max_possible'
        ]);
        $this->weight = 0;
    }

    public function updatedDepartmentPlanItemId($value)
    {
        if ($value) {
            $deptItem = DepartmentPlanItem::find($value);
            if ($deptItem) {
                $this->item_name = $deptItem->item_name;
                $this->item_description = $deptItem->description;
                $this->weight = $deptItem->weight;
                $this->target_value = $deptItem->target_value;
                $this->target_unit = $deptItem->target_unit;
                $this->min_acceptable = $deptItem->min_acceptable;
                $this->max_possible = $deptItem->max_possible;
            }
        }
    }

    public function saveItem()
    {
        $this->validate([
            'employee_plan_id' => ['required', 'exists:employee_plans,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'item_description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_value' => ['nullable', 'numeric', 'min:0'],
            'target_unit' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [
            'employee_plan_id' => $this->employee_plan_id,
            'department_plan_item_id' => $this->department_plan_item_id ?: null,
            'item_name' => $this->item_name,
            'description' => $this->item_description,
            'weight' => $this->weight,
            'target_value' => $this->target_value,
            'target_unit' => $this->target_unit,
            'min_acceptable' => $this->min_acceptable,
            'max_possible' => $this->max_possible,
            'is_active' => true,
        ];

        if ($this->modalMode === 'edit' && $this->itemId) {
            $item = EmployeePlanItem::findOrFail($this->itemId);
            $item->update($data);
            session()->flash('success', 'Goal updated successfully!');
        } else {
            EmployeePlanItem::create($data);
            session()->flash('success', 'Goal added successfully!');
        }

        $this->showItemModal = false;
        $this->resetItemForm();
        $this->loadMyPlans();
    }

    public function deleteItem($id)
    {
        $item = EmployeePlanItem::findOrFail($id);
        $plan = EmployeePlan::where('id', $item->employee_plan_id)
            ->where('employee_id', $this->employee->id)
            ->firstOrFail();
        $item->delete();
        session()->flash('success', 'Goal deleted successfully!');
        $this->loadMyPlans();
    }

    public function submitPlanForReview($planId)
    {
        $plan = EmployeePlan::where('id', $planId)
            ->where('employee_id', $this->employee->id)
            ->firstOrFail();

        $plan->update(['status' => 'active']);
        session()->flash('success', 'Plan submitted for review!');
        $this->loadMyPlans();
    }

    public function getDepartmentPlanItemsForPlan($planId)
    {
        $plan = EmployeePlan::find($planId);
        if ($plan && $plan->department_plan_id) {
            return DepartmentPlanItem::where('department_plan_id', $plan->department_plan_id)
                ->where('is_active', true)
                ->get();
        }
        return collect();
    }

    public function render()
    {
        return view('livewire.performance.staffs.myplanning');
    }
}

<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanImplementation;
use App\Models\EmployeePlanItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Myimplimentations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $employee;
    public $hasEmployeeRecord = false;
    public $myPlans = [];
    public $selectedPlan = null;
    public $selectedPlanItems = [];

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';

    // Implementation form fields
    public $implementationId;
    public $employee_plan_item_id;
    public $implementation_date;
    public $activity_title;
    public $activity_description;
    public $quantity_achieved;
    public $unit;
    public $evidence;
    public $challenges;
    public $lessons_learned;

    // Filter
    public $search = '';
    public $filterPlanId = '';
    public $filterStatus = '';

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->loadMyPlans();
            $this->implementation_date = date('Y-m-d');
        }
    }

    public function loadMyPlans()
    {
        if (!$this->employee) return;

        $this->myPlans = EmployeePlan::where('employee_id', $this->employee->id)
            ->whereIn('status', ['active', 'completed'])
            ->with('employeePlanItems')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = EmployeePlan::with('employeePlanItems.implementations')->find($planId);
        $this->selectedPlanItems = $this->selectedPlan?->employeePlanItems ?? [];
        $this->filterPlanId = $planId;
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $impl = EmployeePlanImplementation::where('id', $id)
                ->where('employee_id', $this->employee->id)
                ->firstOrFail();

            $this->implementationId = $impl->id;
            $this->employee_plan_item_id = $impl->employee_plan_item_id;
            $this->implementation_date = $impl->implementation_date->format('Y-m-d');
            $this->activity_title = $impl->activity_title;
            $this->activity_description = $impl->activity_description;
            $this->quantity_achieved = $impl->quantity_achieved;
            $this->unit = $impl->unit;
            $this->evidence = $impl->evidence;
            $this->challenges = $impl->challenges;
            $this->lessons_learned = $impl->lessons_learned;
        } else {
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'implementationId', 'employee_plan_item_id', 'activity_title',
            'activity_description', 'quantity_achieved', 'unit',
            'evidence', 'challenges', 'lessons_learned'
        ]);
        $this->implementation_date = date('Y-m-d');
    }

    public function updatedEmployeePlanItemId($value)
    {
        if ($value) {
            $item = EmployeePlanItem::find($value);
            if ($item) {
                $this->unit = $item->target_unit;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'employee_plan_item_id' => ['required', 'exists:employee_plan_items,id'],
            'implementation_date' => ['required', 'date', 'before_or_equal:today'],
            'activity_title' => ['required', 'string', 'max:255'],
            'activity_description' => ['nullable', 'string'],
            'quantity_achieved' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'evidence' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'lessons_learned' => ['nullable', 'string'],
        ]);

        $data = [
            'employee_plan_item_id' => $this->employee_plan_item_id,
            'employee_id' => $this->employee->id,
            'implementation_date' => $this->implementation_date,
            'activity_title' => $this->activity_title,
            'activity_description' => $this->activity_description,
            'quantity_achieved' => $this->quantity_achieved,
            'unit' => $this->unit,
            'evidence' => $this->evidence,
            'challenges' => $this->challenges,
            'lessons_learned' => $this->lessons_learned,
            'status' => 'pending',
            'added_by' => Auth::id(),
        ];

        if ($this->modalMode === 'edit' && $this->implementationId) {
            $impl = EmployeePlanImplementation::where('id', $this->implementationId)
                ->where('employee_id', $this->employee->id)
                ->firstOrFail();

            // Only allow edit if not yet verified
            if ($impl->status !== 'verified') {
                $impl->update($data);
                session()->flash('success', 'Implementation updated successfully!');
            } else {
                session()->flash('error', 'Cannot edit verified implementations.');
            }
        } else {
            EmployeePlanImplementation::create($data);
            session()->flash('success', 'Implementation recorded successfully!');
        }

        $this->showModal = false;
        $this->resetForm();

        // Refresh selected plan if exists
        if ($this->selectedPlan) {
            $this->selectPlan($this->selectedPlan->id);
        }
    }

    public function delete($id)
    {
        $impl = EmployeePlanImplementation::where('id', $id)
            ->where('employee_id', $this->employee->id)
            ->firstOrFail();

        // Only allow delete if not yet verified
        if ($impl->status !== 'verified') {
            $impl->delete();
            session()->flash('success', 'Implementation deleted successfully!');
        } else {
            session()->flash('error', 'Cannot delete verified implementations.');
        }

        // Refresh selected plan if exists
        if ($this->selectedPlan) {
            $this->selectPlan($this->selectedPlan->id);
        }
    }

    public function getAllPlanItems()
    {
        if (!$this->employee) return collect();

        return EmployeePlanItem::whereHas('employeePlan', function ($query) {
            $query->where('employee_id', $this->employee->id)
                ->whereIn('status', ['active', 'completed']);
        })->with('employeePlan')->get();
    }

    public function render()
    {
        $implementations = EmployeePlanImplementation::where('employee_id', $this->employee?->id)
            ->when($this->search, function ($query) {
                $query->where('activity_title', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterPlanId, function ($query) {
                $query->whereHas('employeePlanItem', function ($q) {
                    $q->where('employee_plan_id', $this->filterPlanId);
                });
            })
            ->with(['employeePlanItem.employeePlan'])
            ->orderBy('implementation_date', 'desc')
            ->paginate(10);

        return view('livewire.performance.staffs.myimplimentations', [
            'implementations' => $implementations,
            'allPlanItems' => $this->getAllPlanItems(),
        ]);
    }
}

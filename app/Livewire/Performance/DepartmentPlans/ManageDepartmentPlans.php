<?php

namespace App\Livewire\Performance\DepartmentPlans;

use App\Models\DepartmentPlan;
use App\Models\DepartmentPlanItem;
use App\Models\OrganizationalPlan;
use App\Models\departments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageDepartmentPlans extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';
    public $departmentFilter = '';
    public $statusFilter = '';

    // Modal States
    public $showModal = false;
    public $modalMode = 'create';

    // Selected Records
    public $selectedPlan = null;

    // Plan Form Fields
    public $plan_id;
    public $organizational_plan_id;
    public $department_id;
    public $plan_name;
    public $description;
    public $status = 'draft';

    // Pagination reset on search/filter changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        //
    }

    // Plan CRUD Operations
    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->showModal = true;
        $this->resetPlanForm();
        $this->status = 'draft';
    }

    public function openEditModal($planId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->showModal = true;

        $plan = DepartmentPlan::findOrFail($planId);
        $this->plan_id = $plan->id;
        $this->organizational_plan_id = $plan->organizational_plan_id;
        $this->department_id = $plan->department_id;
        $this->plan_name = $plan->plan_name;
        $this->description = $plan->description;
        $this->status = $plan->status;
    }

    public function save()
    {
        $this->validate($this->getValidationRules());

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->plan_id) {
                $plan = DepartmentPlan::findOrFail($this->plan_id);
                $plan->update([
                    'organizational_plan_id' => $this->organizational_plan_id,
                    'department_id' => $this->department_id,
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'status' => $this->status,
                ]);
                session()->flash('success', 'Department Plan updated successfully!');
            } else {
                DepartmentPlan::create([
                    'organizational_plan_id' => $this->organizational_plan_id,
                    'department_id' => $this->department_id,
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
                session()->flash('success', 'Department Plan created successfully!');
            }

            DB::commit();
            $this->showModal = false;
            $this->resetPlanForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function delete($planId)
    {
        try {
            $plan = DepartmentPlan::findOrFail($planId);

            // Check if plan has employee plans
            if ($plan->employeePlans()->count() > 0) {
                session()->flash('error', 'Cannot delete plan with existing employee plans!');
                return;
            }

            // Check if plan is approved
            if ($plan->status === 'approved') {
                session()->flash('error', 'Cannot delete an approved plan!');
                return;
            }

            DB::beginTransaction();
            $plan->delete();
            DB::commit();

            session()->flash('success', 'Department Plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function distributePlanItems($planId)
    {
        try {
            $plan = DepartmentPlan::with(['organizationalPlan.items', 'departmentPlanItems'])
                ->findOrFail($planId);

            // Check if organizational plan has items
            if ($plan->organizationalPlan->items->count() === 0) {
                session()->flash('error', 'Organizational plan has no items to distribute!');
                return;
            }

            // Check if items are already distributed
            if ($plan->departmentPlanItems->count() > 0) {
                session()->flash('error', 'Plan items already distributed! Delete existing items first.');
                return;
            }

            DB::beginTransaction();

            // Copy items from organizational plan
            foreach ($plan->organizationalPlan->items as $orgItem) {
                DepartmentPlanItem::create([
                    'department_plan_id' => $plan->id,
                    'organizational_plan_item_id' => $orgItem->id,
                    'item_name' => $orgItem->item_name,
                    'description' => $orgItem->description,
                    'kpi_type' => $orgItem->kpi_type,
                    'measurement_type' => $orgItem->measurement_type,
                    'weight' => $orgItem->weight,
                    'target_value' => $orgItem->target_value,
                    'target_unit' => $orgItem->target_unit,
                    'min_acceptable' => $orgItem->min_acceptable,
                    'max_possible' => $orgItem->max_possible,
                    'rating_scale_max' => $orgItem->rating_scale_max,
                    'scoring_criteria' => $orgItem->scoring_criteria,
                    'display_order' => $orgItem->display_order,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            session()->flash('success', 'Plan items distributed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Validation Rules
    protected function getValidationRules()
    {
        return [
            'organizational_plan_id' => ['required', 'exists:organizational_plans,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'plan_name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,approved,completed,cancelled'],
        ];
    }

    // Helper Methods
    protected function resetPlanForm()
    {
        $this->reset([
            'plan_id',
            'organizational_plan_id',
            'department_id',
            'plan_name',
            'description',
            'status'
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetPlanForm();
    }

    public function render()
    {
        // Build query with eager loading
        $plansQuery = DepartmentPlan::query()
            ->with(['organizationalPlan', 'department', 'assignedBy'])
            ->withCount('departmentPlanItems');

        // Apply search filter
        if ($this->search) {
            $plansQuery->where(function ($query) {
                $query->where('plan_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('department', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply department filter
        if ($this->departmentFilter) {
            $plansQuery->where('department_id', $this->departmentFilter);
        }

        // Apply status filter
        if ($this->statusFilter) {
            $plansQuery->where('status', $this->statusFilter);
        }

        // Order by latest
        $plansQuery->orderBy('created_at', 'desc');

        // Paginate
        $plans = $plansQuery->paginate(10);

        // Calculate statistics
        $totalPlans = DepartmentPlan::count();
        $activePlans = DepartmentPlan::where('status', 'active')->count();
        $completedPlans = DepartmentPlan::where('status', 'completed')->count();
        $departmentsCovered = DepartmentPlan::distinct('department_id')->count('department_id');

        // Get departments and organizational plans for filters
        $departments = departments::orderBy('name')->get();
        $organizationalPlans = OrganizationalPlan::where('status', 'approved')
            ->orderBy('plan_name')
            ->get();

        return view('livewire.performance.department-plans.manage-department-plans', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'activePlans' => $activePlans,
            'completedPlans' => $completedPlans,
            'departmentsCovered' => $departmentsCovered,
            'departments' => $departments,
            'organizationalPlans' => $organizationalPlans,
        ]);
    }
}

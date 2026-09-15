<?php

namespace App\Livewire\Performance\DepartmentPlans;

use App\Models\DepartmentPlan;
use App\Models\DepartmentPlanItem;
use App\Models\departments;
use App\Models\OrganizationalPlan;
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

    public $perPage = 10;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    // Selected Records
    public $editingPlanId = null;

    // Plan Form Fields
    public $planForm = [
        'organizational_plan_id' => '',
        'department_id' => '',
        'plan_name' => '',
        'description' => '',
        'status' => 'draft',
    ];

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

    public function resetFilters()
    {
        $this->reset(['search', 'departmentFilter', 'statusFilter']);
        $this->resetPage();
    }

    // Plan CRUD Operations
    public function createPlan()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingPlanId = null;
        $this->planForm = [
            'organizational_plan_id' => '',
            'department_id' => '',
            'plan_name' => '',
            'description' => '',
            'status' => 'draft',
        ];
        $this->showModal = true;
    }

    public function editPlan($planId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingPlanId = $planId;

        $plan = DepartmentPlan::findOrFail($planId);
        $this->planForm = [
            'organizational_plan_id' => $plan->organizational_plan_id,
            'department_id' => $plan->department_id,
            'plan_name' => $plan->plan_name,
            'description' => $plan->description,
            'status' => $plan->status,
        ];
        $this->showModal = true;
    }

    public function savePlan()
    {
        $rules = [
            'planForm.organizational_plan_id' => 'required|exists:organizational_plans,id',
            'planForm.department_id' => 'required|exists:departments,id',
            'planForm.plan_name' => 'required|string|max:255',
            'planForm.description' => 'nullable|string',
            'planForm.status' => 'required|in:draft,active,completed,cancelled',
        ];

        $messages = [
            'planForm.organizational_plan_id.required' => 'Please select an organizational plan.',
            'planForm.organizational_plan_id.exists' => 'The selected organizational plan is invalid.',
            'planForm.department_id.required' => 'Please select a department.',
            'planForm.department_id.exists' => 'The selected department is invalid.',
            'planForm.plan_name.required' => 'Please enter the plan name.',
            'planForm.status.required' => 'Please select a status.',
        ];

        $this->validate($rules, $messages);

        // Check for duplicate department-organizational plan combination
        $existingPlan = DepartmentPlan::where('organizational_plan_id', $this->planForm['organizational_plan_id'])
            ->where('department_id', $this->planForm['department_id'])
            ->when($this->editingPlanId, fn ($q) => $q->where('id', '!=', $this->editingPlanId))
            ->first();

        if ($existingPlan) {
            session()->flash('error', 'This department already has a plan linked to this organizational plan.');

            return;
        }

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->editingPlanId) {
                $plan = DepartmentPlan::findOrFail($this->editingPlanId);
                $plan->update([
                    'organizational_plan_id' => $this->planForm['organizational_plan_id'],
                    'department_id' => $this->planForm['department_id'],
                    'plan_name' => $this->planForm['plan_name'],
                    'description' => $this->planForm['description'],
                    'status' => $this->planForm['status'],
                ]);
                session()->flash('success', 'Department plan updated successfully!');
            } else {
                DepartmentPlan::create([
                    'organizational_plan_id' => $this->planForm['organizational_plan_id'],
                    'department_id' => $this->planForm['department_id'],
                    'plan_name' => $this->planForm['plan_name'],
                    'description' => $this->planForm['description'],
                    'status' => $this->planForm['status'],
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
                session()->flash('success', 'Department plan created successfully!');
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
        $this->editingPlanId = null;
        $this->resetErrorBag();
    }

    public function deletePlan($planId)
    {
        try {
            $plan = DepartmentPlan::findOrFail($planId);

            // Check if plan has employee plans
            if (method_exists($plan, 'employeePlans') && $plan->employeePlans()->count() > 0) {
                session()->flash('error', 'Cannot delete this plan because it has employee plans assigned to it.');

                return;
            }

            // Check if plan is active or completed
            if (in_array($plan->status, ['active', 'completed'])) {
                session()->flash('error', 'Cannot delete an active or completed plan.');

                return;
            }

            DB::beginTransaction();
            // Delete related items first
            if (method_exists($plan, 'departmentPlanItems')) {
                $plan->departmentPlanItems()->delete();
            }
            $plan->delete();
            DB::commit();

            session()->flash('success', 'Department plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function distributePlanItems($planId)
    {
        try {
            $plan = DepartmentPlan::with(['organizationalPlan.items', 'departmentPlanItems'])
                ->findOrFail($planId);

            // Check if organizational plan has items
            if (! $plan->organizationalPlan || $plan->organizationalPlan->items->count() === 0) {
                session()->flash('error', 'The linked organizational plan has no items to distribute.');

                return;
            }

            // Check if items are already distributed
            if ($plan->departmentPlanItems->count() > 0) {
                session()->flash('error', 'Plan items already distributed. Delete existing items first to re-distribute.');

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
                    'min_acceptable' => $orgItem->min_acceptable ?? null,
                    'max_possible' => $orgItem->max_possible ?? null,
                    'rating_scale_max' => $orgItem->rating_scale_max ?? null,
                    'scoring_criteria' => $orgItem->scoring_criteria ?? null,
                    'display_order' => $orgItem->display_order,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            session()->flash('success', 'Plan items distributed successfully! '.$plan->organizationalPlan->items->count().' items copied.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while distributing items: '.$e->getMessage());
        }
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
                $query->where('plan_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('department', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
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
        $plans = $plansQuery->paginate($this->perPage);

        // Calculate statistics
        $totalPlans = DepartmentPlan::count();
        $activePlans = DepartmentPlan::where('status', 'active')->count();
        $completedPlans = DepartmentPlan::where('status', 'completed')->count();
        $departmentsCovered = DepartmentPlan::distinct('department_id')->count('department_id');

        // Get departments for filter
        $departments = departments::orderBy('name')->get();

        // Get approved organizational plans (plans that have been approved - have approved_at set)
        $organizationalPlans = OrganizationalPlan::whereNotNull('approved_at')
            ->orderBy('plan_name')
            ->get();

        // If no approved plans, get active ones as fallback
        if ($organizationalPlans->isEmpty()) {
            $organizationalPlans = OrganizationalPlan::where('status', 'active')
                ->orderBy('plan_name')
                ->get();
        }

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

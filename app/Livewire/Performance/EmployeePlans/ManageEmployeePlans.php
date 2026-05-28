<?php

namespace App\Livewire\Performance\EmployeePlans;

use App\Models\DepartmentPlan;
use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageEmployeePlans extends Component
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
        'department_plan_id' => '',
        'employee_id' => '',
        'plan_name' => '',
        'description' => '',
        'status' => 'draft',
    ];

    // Searchable employee picker (modal)
    public $planEmployeeSearch = '';

    public $showPlanEmployeeDropdown = false;

    public function getFilteredPlanEmployeesProperty()
    {
        $term = trim($this->planEmployeeSearch);

        return Employee::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                        ->orWhere('employee_no', 'like', '%'.$term.'%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$term.'%']);
                });
            })
            ->orderBy('first_name')
            ->limit(50)
            ->get();
    }

    public function getSelectedPlanEmployeeProperty()
    {
        $id = $this->planForm['employee_id'] ?? null;

        return $id ? Employee::find($id) : null;
    }

    public function selectPlanEmployee($id)
    {
        $this->planForm['employee_id'] = $id;
        $this->planEmployeeSearch = '';
        $this->showPlanEmployeeDropdown = false;
    }

    public function clearPlanEmployee()
    {
        $this->planForm['employee_id'] = '';
        $this->planEmployeeSearch = '';
        $this->showPlanEmployeeDropdown = true;
    }

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
            'department_plan_id' => '',
            'employee_id' => '',
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

        $plan = EmployeePlan::findOrFail($planId);
        $this->planForm = [
            'department_plan_id' => $plan->department_plan_id,
            'employee_id' => $plan->employee_id,
            'plan_name' => $plan->plan_name,
            'description' => $plan->description,
            'status' => $plan->status,
        ];
        $this->showModal = true;
    }

    public function savePlan()
    {
        $rules = [
            'planForm.department_plan_id' => 'required|exists:department_plans,id',
            'planForm.employee_id' => 'required|exists:employees,id',
            'planForm.plan_name' => 'required|string|max:255',
            'planForm.description' => 'nullable|string',
            'planForm.status' => 'required|in:draft,active,completed,cancelled',
        ];

        $messages = [
            'planForm.department_plan_id.required' => 'Please select a department plan.',
            'planForm.department_plan_id.exists' => 'The selected department plan is invalid.',
            'planForm.employee_id.required' => 'Please select an employee.',
            'planForm.employee_id.exists' => 'The selected employee is invalid.',
            'planForm.plan_name.required' => 'Please enter the plan name.',
            'planForm.status.required' => 'Please select a status.',
        ];

        $this->validate($rules, $messages);

        // Check for duplicate employee-department plan combination
        $existingPlan = EmployeePlan::where('department_plan_id', $this->planForm['department_plan_id'])
            ->where('employee_id', $this->planForm['employee_id'])
            ->when($this->editingPlanId, fn ($q) => $q->where('id', '!=', $this->editingPlanId))
            ->first();

        if ($existingPlan) {
            session()->flash('error', 'This employee already has a plan linked to this department plan.');

            return;
        }

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->editingPlanId) {
                $plan = EmployeePlan::findOrFail($this->editingPlanId);
                $plan->update([
                    'department_plan_id' => $this->planForm['department_plan_id'],
                    'employee_id' => $this->planForm['employee_id'],
                    'plan_name' => $this->planForm['plan_name'],
                    'description' => $this->planForm['description'],
                    'status' => $this->planForm['status'],
                ]);
                session()->flash('success', 'Employee plan updated successfully!');
            } else {
                EmployeePlan::create([
                    'department_plan_id' => $this->planForm['department_plan_id'],
                    'employee_id' => $this->planForm['employee_id'],
                    'plan_name' => $this->planForm['plan_name'],
                    'description' => $this->planForm['description'],
                    'status' => $this->planForm['status'],
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
                session()->flash('success', 'Employee plan created successfully!');
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
        $this->planEmployeeSearch = '';
        $this->showPlanEmployeeDropdown = false;
        $this->resetErrorBag();
    }

    public function deletePlan($planId)
    {
        try {
            $plan = EmployeePlan::findOrFail($planId);

            // Check if plan has been reviewed
            if ($plan->reviewed_at !== null) {
                session()->flash('error', 'Cannot delete a plan that has been reviewed.');

                return;
            }

            DB::beginTransaction();
            // Delete related items first
            if (method_exists($plan, 'employeePlanItems')) {
                $plan->employeePlanItems()->delete();
            }
            $plan->delete();
            DB::commit();

            session()->flash('success', 'Employee plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function activatePlan($planId)
    {
        try {
            $plan = EmployeePlan::withCount('employeePlanItems')->findOrFail($planId);

            // Validate that plan has items
            if ($plan->employee_plan_items_count === 0) {
                session()->flash('error', 'Cannot activate a plan without items. Please distribute items first.');

                return;
            }

            // Check if already active
            if ($plan->status === 'active') {
                session()->flash('error', 'This plan is already active.');

                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'active',
            ]);
            DB::commit();

            session()->flash('success', 'Plan activated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function reviewPlan($planId)
    {
        try {
            $plan = EmployeePlan::withCount('employeePlanItems')->findOrFail($planId);

            // Check if already reviewed
            if ($plan->reviewed_at !== null) {
                session()->flash('error', 'This plan has already been reviewed.');

                return;
            }

            // Validate that plan is active
            if ($plan->status !== 'active') {
                session()->flash('error', 'Only active plans can be reviewed.');

                return;
            }

            DB::beginTransaction();
            $plan->update([
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            DB::commit();

            session()->flash('success', 'Employee plan reviewed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function completePlan($planId)
    {
        try {
            $plan = EmployeePlan::findOrFail($planId);

            // Validate that plan has been reviewed
            if ($plan->reviewed_at === null) {
                session()->flash('error', 'Plan must be reviewed before completing.');

                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'completed',
            ]);
            DB::commit();

            session()->flash('success', 'Employee plan marked as completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function distributePlanItems($planId)
    {
        try {
            $plan = EmployeePlan::with(['departmentPlan.departmentPlanItems', 'employeePlanItems'])
                ->findOrFail($planId);

            // Check if department plan has items
            if (! $plan->departmentPlan || $plan->departmentPlan->departmentPlanItems->count() === 0) {
                session()->flash('error', 'The linked department plan has no items to distribute.');

                return;
            }

            // Check if items are already distributed
            if ($plan->employeePlanItems->count() > 0) {
                session()->flash('error', 'Plan items already distributed. Delete existing items first to re-distribute.');

                return;
            }

            DB::beginTransaction();

            // Copy items from department plan
            foreach ($plan->departmentPlan->departmentPlanItems as $deptItem) {
                EmployeePlanItem::create([
                    'employee_plan_id' => $plan->id,
                    'department_plan_item_id' => $deptItem->id,
                    'item_name' => $deptItem->item_name,
                    'description' => $deptItem->description,
                    'kpi_type' => $deptItem->kpi_type,
                    'measurement_type' => $deptItem->measurement_type,
                    'weight' => $deptItem->weight,
                    'target_value' => $deptItem->target_value,
                    'target_unit' => $deptItem->target_unit,
                    'min_acceptable' => $deptItem->min_acceptable ?? null,
                    'max_possible' => $deptItem->max_possible ?? null,
                    'rating_scale_max' => $deptItem->rating_scale_max ?? null,
                    'scoring_criteria' => $deptItem->scoring_criteria ?? null,
                    'display_order' => $deptItem->display_order,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            session()->flash('success', 'Plan items distributed successfully! '.$plan->departmentPlan->departmentPlanItems->count().' items copied.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while distributing items: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Build query with eager loading
        $plansQuery = EmployeePlan::query()
            ->with(['departmentPlan.department', 'employee', 'assignedBy', 'reviewedBy'])
            ->withCount('employeePlanItems');

        // Apply search filter
        if ($this->search) {
            $plansQuery->where(function ($query) {
                $query->where('plan_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('employee', function ($q) {
                        $q->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%')
                            ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Apply department filter
        if ($this->departmentFilter) {
            $plansQuery->whereHas('employee', function ($query) {
                $query->where('department_id', $this->departmentFilter);
            });
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
        $totalPlans = EmployeePlan::count();
        $activePlans = EmployeePlan::where('status', 'active')->count();
        $reviewedPlans = EmployeePlan::whereNotNull('reviewed_at')->count();
        $completedPlans = EmployeePlan::where('status', 'completed')->count();

        // Get departments for filter
        $departments = departments::orderBy('name')->get();

        // Get employees for modal
        $employees = Employee::orderBy('first_name')->get();

        // Get active department plans (that have been activated)
        $departmentPlans = DepartmentPlan::where('status', 'active')
            ->with('department')
            ->orderBy('plan_name')
            ->get();

        return view('livewire.performance.employee-plans.manage-employee-plans', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'activePlans' => $activePlans,
            'reviewedPlans' => $reviewedPlans,
            'completedPlans' => $completedPlans,
            'departments' => $departments,
            'employees' => $employees,
            'departmentPlans' => $departmentPlans,
        ]);
    }
}

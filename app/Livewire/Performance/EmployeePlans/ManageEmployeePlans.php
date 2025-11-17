<?php

namespace App\Livewire\Performance\EmployeePlans;

use App\Models\EmployeePlan;
use App\Models\EmployeePlanItem;
use App\Models\DepartmentPlan;
use App\Models\Employee;
use App\Models\departments;
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
    public $reviewStatusFilter = '';

    // Modal States
    public $showModal = false;
    public $modalMode = 'create';

    // Selected Records
    public $selectedPlan = null;

    // Plan Form Fields
    public $plan_id;
    public $department_plan_id;
    public $employee_id;
    public $plan_name;
    public $description;
    public $status = 'draft';
    public $review_comments;

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

    public function updatingReviewStatusFilter()
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

        $plan = EmployeePlan::findOrFail($planId);
        $this->plan_id = $plan->id;
        $this->department_plan_id = $plan->department_plan_id;
        $this->employee_id = $plan->employee_id;
        $this->plan_name = $plan->plan_name;
        $this->description = $plan->description;
        $this->status = $plan->status;
        $this->review_comments = $plan->review_comments;
    }

    public function save()
    {
        $this->validate($this->getValidationRules());

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->plan_id) {
                $plan = EmployeePlan::findOrFail($this->plan_id);
                $plan->update([
                    'department_plan_id' => $this->department_plan_id,
                    'employee_id' => $this->employee_id,
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'review_comments' => $this->review_comments,
                ]);
                session()->flash('success', 'Employee Plan updated successfully!');
            } else {
                EmployeePlan::create([
                    'department_plan_id' => $this->department_plan_id,
                    'employee_id' => $this->employee_id,
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
                session()->flash('success', 'Employee Plan created successfully!');
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
            $plan = EmployeePlan::findOrFail($planId);

            // Check if plan is under review or approved
            if (in_array($plan->status, ['under_review', 'approved'])) {
                session()->flash('error', 'Cannot delete a plan that is under review or approved!');
                return;
            }

            DB::beginTransaction();
            $plan->delete();
            DB::commit();

            session()->flash('success', 'Employee Plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function submitForReview($planId)
    {
        try {
            $plan = EmployeePlan::with('employeePlanItems')->findOrFail($planId);

            // Validate that plan has items
            if ($plan->employeePlanItems->count() === 0) {
                session()->flash('error', 'Cannot submit a plan without items!');
                return;
            }

            // Check if already submitted
            if ($plan->status === 'under_review') {
                session()->flash('error', 'Plan is already under review!');
                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'under_review',
            ]);
            DB::commit();

            session()->flash('success', 'Plan submitted for review successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function approve($planId)
    {
        try {
            $plan = EmployeePlan::with('employeePlanItems')->findOrFail($planId);

            // Validate that plan is under review
            if ($plan->status !== 'under_review') {
                session()->flash('error', 'Only plans under review can be approved!');
                return;
            }

            // Validate that plan has items
            if ($plan->employeePlanItems->count() === 0) {
                session()->flash('error', 'Cannot approve a plan without items!');
                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            DB::commit();

            session()->flash('success', 'Employee Plan approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Validation Rules
    protected function getValidationRules()
    {
        return [
            'department_plan_id' => ['required', 'exists:department_plans,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'plan_name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,under_review,approved,completed,cancelled'],
            'review_comments' => ['nullable', 'string'],
        ];
    }

    // Helper Methods
    protected function resetPlanForm()
    {
        $this->reset([
            'plan_id',
            'department_plan_id',
            'employee_id',
            'plan_name',
            'description',
            'status',
            'review_comments'
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
        $plansQuery = EmployeePlan::query()
            ->with(['departmentPlan', 'employee.user', 'assignedBy', 'reviewedBy'])
            ->withCount('employeePlanItems');

        // Apply search filter
        if ($this->search) {
            $plansQuery->where(function ($query) {
                $query->where('plan_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('employee.user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
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

        // Apply review status filter
        if ($this->reviewStatusFilter) {
            if ($this->reviewStatusFilter === 'reviewed') {
                $plansQuery->whereNotNull('reviewed_by');
            } elseif ($this->reviewStatusFilter === 'pending') {
                $plansQuery->whereNull('reviewed_by');
            }
        }

        // Order by latest
        $plansQuery->orderBy('created_at', 'desc');

        // Paginate
        $plans = $plansQuery->paginate(10);

        // Calculate statistics
        $totalPlans = EmployeePlan::count();
        $activePlans = EmployeePlan::where('status', 'active')->count();
        $underReviewPlans = EmployeePlan::where('status', 'under_review')->count();
        $completedPlans = EmployeePlan::where('status', 'completed')->count();

        // Get departments, employees, and department plans for filters
        $departments = departments::orderBy('name')->get();
        $employees = Employee::with('user')->get();
        $departmentPlans = DepartmentPlan::where('status', 'approved')
            ->with('department')
            ->orderBy('plan_name')
            ->get();

        return view('livewire.performance.employee-plans.manage-employee-plans', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'activePlans' => $activePlans,
            'underReviewPlans' => $underReviewPlans,
            'completedPlans' => $completedPlans,
            'departments' => $departments,
            'employees' => $employees,
            'departmentPlans' => $departmentPlans,
        ]);
    }
}

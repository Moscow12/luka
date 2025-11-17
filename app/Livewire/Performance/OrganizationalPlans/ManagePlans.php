<?php

namespace App\Livewire\Performance\OrganizationalPlans;

use App\Models\OrganizationalPlan;
use App\Models\OrganizationalPlanItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePlans extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';
    public $selectedStatus = '';
    public $selectedPeriodType = '';

    // Modal States
    public $showModal = false;
    public $showItemsModal = false;
    public $modalMode = 'create';
    public $itemModalMode = 'create';

    // Selected Records
    public $selectedPlan = null;
    public $selectedItem = null;

    // Plan Form Fields
    public $plan_id;
    public $plan_name;
    public $description;
    public $start_date;
    public $end_date;
    public $period_type = 'annual';
    public $status = 'draft';

    // Item Form Fields
    public $item_id;
    public $item_name;
    public $item_description;
    public $kpi_type = 'quantitative';
    public $measurement_type = 'numeric';
    public $weight;
    public $target_value;
    public $target_unit;
    public $min_acceptable;
    public $max_possible;
    public $rating_scale_max = 5;
    public $scoring_criteria;
    public $display_order;

    // Pagination reset on search/filter changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSelectedPeriodType()
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
        $this->period_type = 'annual';
        $this->status = 'draft';
    }

    public function openEditModal($planId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->showModal = true;

        $plan = OrganizationalPlan::findOrFail($planId);
        $this->plan_id = $plan->id;
        $this->plan_name = $plan->plan_name;
        $this->description = $plan->description;
        $this->start_date = $plan->start_date->format('Y-m-d');
        $this->end_date = $plan->end_date->format('Y-m-d');
        $this->period_type = $plan->period_type;
        $this->status = $plan->status;
    }

    public function savePlan()
    {
        $this->validate($this->getPlanValidationRules());

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->plan_id) {
                $plan = OrganizationalPlan::findOrFail($this->plan_id);
                $plan->update([
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'period_type' => $this->period_type,
                    'status' => $this->status,
                ]);
                session()->flash('success', 'Organizational Plan updated successfully!');
            } else {
                OrganizationalPlan::create([
                    'plan_name' => $this->plan_name,
                    'description' => $this->description,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'period_type' => $this->period_type,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                ]);
                session()->flash('success', 'Organizational Plan created successfully!');
            }

            DB::commit();
            $this->showModal = false;
            $this->resetPlanForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function deletePlan($planId)
    {
        try {
            $plan = OrganizationalPlan::findOrFail($planId);

            // Check if plan has department plans
            if ($plan->departmentPlans()->count() > 0) {
                session()->flash('error', 'Cannot delete plan with existing department plans!');
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

            session()->flash('success', 'Organizational Plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function approvePlan($planId)
    {
        try {
            $plan = OrganizationalPlan::findOrFail($planId);

            // Validate that plan has items
            if ($plan->items()->count() === 0) {
                session()->flash('error', 'Cannot approve a plan without items!');
                return;
            }

            // Validate total weight equals 100
            $totalWeight = $plan->items()->sum('weight');
            if ($totalWeight != 100) {
                session()->flash('error', "Cannot approve plan! Total weight of items is {$totalWeight}%, must be exactly 100%.");
                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            DB::commit();

            session()->flash('success', 'Organizational Plan approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Plan Items CRUD Operations
    public function openItemsModal($planId)
    {
        $this->selectedPlan = OrganizationalPlan::with('items')->findOrFail($planId);
        $this->showItemsModal = true;
        $this->resetItemForm();
    }

    public function closeItemsModal()
    {
        $this->showItemsModal = false;
        $this->selectedPlan = null;
        $this->resetItemForm();
    }

    public function openCreateItemModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->itemModalMode = 'create';
        $this->resetItemForm();
        $this->kpi_type = 'quantitative';
        $this->measurement_type = 'numeric';
        $this->rating_scale_max = 5;
    }

    public function openEditItemModal($itemId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->itemModalMode = 'edit';

        $item = OrganizationalPlanItem::findOrFail($itemId);
        $this->item_id = $item->id;
        $this->item_name = $item->item_name;
        $this->item_description = $item->description;
        $this->kpi_type = $item->kpi_type;
        $this->measurement_type = $item->measurement_type;
        $this->weight = $item->weight;
        $this->target_value = $item->target_value;
        $this->target_unit = $item->target_unit;
        $this->min_acceptable = $item->min_acceptable;
        $this->max_possible = $item->max_possible;
        $this->rating_scale_max = $item->rating_scale_max;
        $this->scoring_criteria = $item->scoring_criteria;
        $this->display_order = $item->display_order;
    }

    public function saveItem()
    {
        if (!$this->selectedPlan) {
            session()->flash('error', 'No plan selected!');
            return;
        }

        $this->validate($this->getItemValidationRules());

        try {
            DB::beginTransaction();

            if ($this->itemModalMode === 'edit' && $this->item_id) {
                $item = OrganizationalPlanItem::findOrFail($this->item_id);
                $item->update([
                    'item_name' => $this->item_name,
                    'description' => $this->item_description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'min_acceptable' => $this->min_acceptable,
                    'max_possible' => $this->max_possible,
                    'rating_scale_max' => $this->rating_scale_max,
                    'scoring_criteria' => $this->scoring_criteria,
                    'display_order' => $this->display_order,
                ]);
                session()->flash('success', 'Plan Item updated successfully!');
            } else {
                OrganizationalPlanItem::create([
                    'organizational_plan_id' => $this->selectedPlan->id,
                    'item_name' => $this->item_name,
                    'description' => $this->item_description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'min_acceptable' => $this->min_acceptable,
                    'max_possible' => $this->max_possible,
                    'rating_scale_max' => $this->rating_scale_max,
                    'scoring_criteria' => $this->scoring_criteria,
                    'display_order' => $this->display_order ?? 0,
                    'is_active' => true,
                ]);
                session()->flash('success', 'Plan Item created successfully!');
            }

            DB::commit();
            $this->resetItemForm();
            $this->itemModalMode = 'create';

            // Refresh selected plan
            $this->selectedPlan = OrganizationalPlan::with('items')->findOrFail($this->selectedPlan->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function deleteItem($itemId)
    {
        try {
            $item = OrganizationalPlanItem::findOrFail($itemId);

            // Check if item has department plan items
            if ($item->departmentPlanItems()->count() > 0) {
                session()->flash('error', 'Cannot delete item with existing department plan items!');
                return;
            }

            DB::beginTransaction();
            $item->delete();
            DB::commit();

            session()->flash('success', 'Plan Item deleted successfully!');

            // Refresh selected plan
            if ($this->selectedPlan) {
                $this->selectedPlan = OrganizationalPlan::with('items')->findOrFail($this->selectedPlan->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Validation Rules
    protected function getPlanValidationRules()
    {
        return [
            'plan_name' => [
                'required',
                'string',
                'max:255',
                $this->modalMode === 'create'
                    ? 'unique:organizational_plans,plan_name'
                    : 'unique:organizational_plans,plan_name,' . $this->plan_id
            ],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'period_type' => ['required', 'in:monthly,quarterly,semi-annual,annual'],
            'status' => ['required', 'in:draft,active,approved,completed,cancelled'],
        ];
    }

    protected function getItemValidationRules()
    {
        return [
            'item_name' => ['required', 'string', 'max:255'],
            'item_description' => ['nullable', 'string'],
            'kpi_type' => ['required', 'in:quantitative,qualitative'],
            'measurement_type' => ['required', 'in:numeric,percentage,rating,binary,text'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string', 'max:100'],
            'min_acceptable' => ['nullable', 'numeric'],
            'max_possible' => ['nullable', 'numeric'],
            'rating_scale_max' => ['nullable', 'integer', 'min:1', 'max:10'],
            'scoring_criteria' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    // Helper Methods
    protected function resetPlanForm()
    {
        $this->reset([
            'plan_id',
            'plan_name',
            'description',
            'start_date',
            'end_date',
            'period_type',
            'status'
        ]);
    }

    protected function resetItemForm()
    {
        $this->reset([
            'item_id',
            'item_name',
            'item_description',
            'kpi_type',
            'measurement_type',
            'weight',
            'target_value',
            'target_unit',
            'min_acceptable',
            'max_possible',
            'rating_scale_max',
            'scoring_criteria',
            'display_order'
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
        $plansQuery = OrganizationalPlan::query()
            ->with(['creator', 'items'])
            ->withCount('items');

        // Apply search filter
        if ($this->search) {
            $plansQuery->where(function ($query) {
                $query->where('plan_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->selectedStatus) {
            $plansQuery->where('status', $this->selectedStatus);
        }

        // Apply period type filter
        if ($this->selectedPeriodType) {
            $plansQuery->where('period_type', $this->selectedPeriodType);
        }

        // Order by latest
        $plansQuery->orderBy('created_at', 'desc');

        // Paginate
        $plans = $plansQuery->paginate(10);

        // Calculate statistics
        $totalPlans = OrganizationalPlan::count();
        $activePlans = OrganizationalPlan::where('status', 'active')->count();
        $completedPlans = OrganizationalPlan::where('status', 'completed')->count();
        $approvedPlans = OrganizationalPlan::where('status', 'approved')->count();

        return view('livewire.performance.organizational-plans.manage-plans', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'activePlans' => $activePlans,
            'completedPlans' => $completedPlans,
            'approvedPlans' => $approvedPlans,
        ]);
    }
}

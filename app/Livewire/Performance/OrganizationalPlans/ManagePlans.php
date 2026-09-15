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

    public $statusFilter = '';

    public $periodTypeFilter = '';

    public $perPage = 10;

    // Modal States
    public $showPlanModal = false;

    public $showItemsModal = false;

    public $showItemForm = false;

    public $modalMode = 'create';

    public $itemModalMode = 'create';

    // Selected Records
    public $selectedPlan = null;

    public $editingPlanId = null;

    public $editingItemId = null;

    public $planItems = [];

    // Plan Form Fields
    public $planForm = [
        'name' => '',
        'description' => '',
        'start_date' => '',
        'end_date' => '',
        'period_type' => 'yearly',
        'status' => 'draft',
    ];

    // Item Form Fields
    public $itemForm = [
        'name' => '',
        'description' => '',
        'weight' => '',
        'target' => '',
        'unit' => '',
    ];

    // Pagination reset on search/filter changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPeriodTypeFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'periodTypeFilter']);
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
            'name' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
            'period_type' => 'yearly',
            'status' => 'draft',
        ];
        $this->showPlanModal = true;
    }

    public function editPlan($planId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingPlanId = $planId;

        $plan = OrganizationalPlan::findOrFail($planId);
        $this->planForm = [
            'name' => $plan->plan_name,
            'description' => $plan->description,
            'start_date' => $plan->start_date->format('Y-m-d'),
            'end_date' => $plan->end_date->format('Y-m-d'),
            'period_type' => $plan->period_type,
            'status' => $plan->status,
        ];
        $this->showPlanModal = true;
    }

    public function savePlan()
    {
        $rules = [
            'planForm.name' => 'required|string|max:255',
            'planForm.description' => 'nullable|string',
            'planForm.start_date' => 'required|date',
            'planForm.end_date' => 'required|date|after:planForm.start_date',
            'planForm.period_type' => 'required|in:monthly,quarterly,yearly',
            'planForm.status' => 'required|in:draft,active,completed,cancelled',
        ];

        $messages = [
            'planForm.name.required' => 'Please enter the plan name.',
            'planForm.start_date.required' => 'Please select a start date.',
            'planForm.end_date.required' => 'Please select an end date.',
            'planForm.end_date.after' => 'End date must be after start date.',
            'planForm.period_type.required' => 'Please select a period type.',
            'planForm.status.required' => 'Please select a status.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->editingPlanId) {
                $plan = OrganizationalPlan::findOrFail($this->editingPlanId);
                $plan->update([
                    'plan_name' => $this->planForm['name'],
                    'description' => $this->planForm['description'],
                    'start_date' => $this->planForm['start_date'],
                    'end_date' => $this->planForm['end_date'],
                    'period_type' => $this->planForm['period_type'],
                    'status' => $this->planForm['status'],
                ]);
                session()->flash('success', 'Plan updated successfully!');
            } else {
                OrganizationalPlan::create([
                    'plan_name' => $this->planForm['name'],
                    'description' => $this->planForm['description'],
                    'start_date' => $this->planForm['start_date'],
                    'end_date' => $this->planForm['end_date'],
                    'period_type' => $this->planForm['period_type'],
                    'status' => $this->planForm['status'],
                    'created_by' => Auth::id(),
                ]);
                session()->flash('success', 'Plan created successfully!');
            }

            DB::commit();
            $this->closePlanModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function closePlanModal()
    {
        $this->showPlanModal = false;
        $this->editingPlanId = null;
        $this->resetErrorBag();
    }

    public function deletePlan($planId)
    {
        try {
            $plan = OrganizationalPlan::findOrFail($planId);

            if ($plan->departmentPlans()->count() > 0) {
                session()->flash('error', 'Cannot delete plan with existing department plans.');

                return;
            }

            if ($plan->status === 'approved') {
                session()->flash('error', 'Cannot delete an approved plan.');

                return;
            }

            DB::beginTransaction();
            $plan->items()->delete();
            $plan->delete();
            DB::commit();

            session()->flash('success', 'Plan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function approvePlan($planId)
    {
        try {
            $plan = OrganizationalPlan::findOrFail($planId);

            if ($plan->items()->count() === 0) {
                session()->flash('error', 'Cannot approve a plan without items.');

                return;
            }

            $totalWeight = $plan->items()->sum('weight');
            if ($totalWeight != 100) {
                session()->flash('error', "Total weight is {$totalWeight}%, must be exactly 100%.");

                return;
            }

            DB::beginTransaction();
            $plan->update([
                'status' => 'active',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            DB::commit();

            session()->flash('success', 'Plan approved and activated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    // Plan Items CRUD Operations
    public function manageItems($planId)
    {
        $this->selectedPlan = OrganizationalPlan::findOrFail($planId);
        $this->loadPlanItems();
        $this->showItemsModal = true;
        $this->showItemForm = false;
        $this->resetItemForm();
    }

    public function loadPlanItems()
    {
        if ($this->selectedPlan) {
            $this->planItems = OrganizationalPlanItem::where('organizational_plan_id', $this->selectedPlan->id)
                ->orderBy('display_order')
                ->get();
        }
    }

    public function closeItemsModal()
    {
        $this->showItemsModal = false;
        $this->selectedPlan = null;
        $this->planItems = [];
        $this->resetItemForm();
    }

    public function addNewItem()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->itemModalMode = 'create';
        $this->editingItemId = null;
        $this->resetItemForm();
        $this->showItemForm = true;
    }

    public function editItem($itemId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->itemModalMode = 'edit';
        $this->editingItemId = $itemId;

        $item = OrganizationalPlanItem::findOrFail($itemId);
        $this->itemForm = [
            'name' => $item->item_name,
            'description' => $item->description,
            'weight' => $item->weight,
            'target' => $item->target_value,
            'unit' => $item->target_unit,
        ];
        $this->showItemForm = true;
    }

    public function saveItem()
    {
        if (! $this->selectedPlan) {
            session()->flash('error', 'No plan selected.');

            return;
        }

        $rules = [
            'itemForm.name' => 'required|string|max:255',
            'itemForm.description' => 'nullable|string',
            'itemForm.weight' => 'required|numeric|min:0|max:100',
            'itemForm.target' => 'nullable|string',
            'itemForm.unit' => 'nullable|string|max:100',
        ];

        $messages = [
            'itemForm.name.required' => 'Please enter the item name.',
            'itemForm.weight.required' => 'Please enter the weight percentage.',
            'itemForm.weight.min' => 'Weight must be at least 0%.',
            'itemForm.weight.max' => 'Weight cannot exceed 100%.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            if ($this->itemModalMode === 'edit' && $this->editingItemId) {
                $item = OrganizationalPlanItem::findOrFail($this->editingItemId);
                $item->update([
                    'item_name' => $this->itemForm['name'],
                    'description' => $this->itemForm['description'],
                    'weight' => $this->itemForm['weight'],
                    'target_value' => $this->itemForm['target'],
                    'target_unit' => $this->itemForm['unit'],
                ]);
                session()->flash('success', 'Item updated successfully!');
            } else {
                $maxOrder = OrganizationalPlanItem::where('organizational_plan_id', $this->selectedPlan->id)->max('display_order') ?? 0;

                OrganizationalPlanItem::create([
                    'organizational_plan_id' => $this->selectedPlan->id,
                    'item_name' => $this->itemForm['name'],
                    'description' => $this->itemForm['description'],
                    'weight' => $this->itemForm['weight'],
                    'target_value' => $this->itemForm['target'],
                    'target_unit' => $this->itemForm['unit'],
                    'kpi_type' => 'quantitative',
                    'measurement_type' => 'numeric',
                    'display_order' => $maxOrder + 1,
                    'is_active' => true,
                ]);
                session()->flash('success', 'Item added successfully!');
            }

            DB::commit();
            $this->loadPlanItems();
            $this->cancelItemForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function deleteItem($itemId)
    {
        try {
            $item = OrganizationalPlanItem::findOrFail($itemId);

            if (method_exists($item, 'departmentPlanItems') && $item->departmentPlanItems()->count() > 0) {
                session()->flash('error', 'Cannot delete item with existing department plan items.');

                return;
            }

            DB::beginTransaction();
            $item->delete();
            DB::commit();

            session()->flash('success', 'Item deleted successfully!');
            $this->loadPlanItems();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function cancelItemForm()
    {
        $this->showItemForm = false;
        $this->editingItemId = null;
        $this->resetItemForm();
    }

    protected function resetItemForm()
    {
        $this->itemForm = [
            'name' => '',
            'description' => '',
            'weight' => '',
            'target' => '',
            'unit' => '',
        ];
    }

    public function render()
    {
        $plansQuery = OrganizationalPlan::query()
            ->with(['creator', 'items'])
            ->withCount('items')
            ->withSum('items', 'weight');

        if ($this->search) {
            $plansQuery->where(function ($query) {
                $query->where('plan_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter) {
            $plansQuery->where('status', $this->statusFilter);
        }

        if ($this->periodTypeFilter) {
            $plansQuery->where('period_type', $this->periodTypeFilter);
        }

        $plansQuery->orderBy('created_at', 'desc');
        $plans = $plansQuery->paginate($this->perPage);

        // Add total_weight accessor to each plan
        $plans->getCollection()->transform(function ($plan) {
            $plan->total_weight = $plan->items_sum_weight ?? 0;
            $plan->name = $plan->plan_name; // alias for blade compatibility

            return $plan;
        });

        return view('livewire.performance.organizational-plans.manage-plans', [
            'plans' => $plans,
            'totalPlans' => OrganizationalPlan::count(),
            'activePlans' => OrganizationalPlan::where('status', 'active')->count(),
            'completedPlans' => OrganizationalPlan::where('status', 'completed')->count(),
            'approvedPlans' => OrganizationalPlan::whereNotNull('approved_at')->count(),
        ]);
    }
}

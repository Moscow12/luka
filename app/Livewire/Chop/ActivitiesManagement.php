<?php

namespace App\Livewire\Chop;

use App\Models\chopactivities;
use App\Models\activityitems;
use App\Models\activitypersonel;
use App\Models\chopcategoryarea;
use App\Models\chopitems;
use App\Models\sourceoffunds;
use App\Models\FinancialYear;
use App\Models\Jobtitle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ActivitiesManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $activity_id;
    public $modalMode = 'create';
    public $showModal = false;

    // Activity fields
    public $planned_activity;
    public $actual_activity;
    public $description;
    public $status = 'pending';
    public $planned_amount = 0;
    public $actual_amount = 0;
    public $percentage = 0;
    public $is_active = true;
    public $is_planned = true;
    public $is_approved = false;
    public $expected_outcome;
    public $expected_outcome_date;
    public $activity_type = 'expenditure';
    public $frequence_monitoring;
    public $source_id;
    public $category_id;
    public $financial_year_id;

    // Items management
    public $selectedItems = [];
    public $itemSearch = '';
    public $showItemSelector = false;

    // Personnel management
    public $selectedPersonnel = [];
    public $showPersonnelSelector = false;

    public function mount()
    {
        // Set default financial year
        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->financial_year_id = $currentFY->id;
        }
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if (in_array($mode, ['edit', 'view']) && $id) {
            $activity = chopactivities::with(['items.item', 'personels.title'])->findOrFail($id);
            $this->activity_id = $id;
            $this->planned_activity = $activity->planned_activity;
            $this->actual_activity = $activity->actual_activity;
            $this->description = $activity->description;
            $this->status = $activity->status;
            $this->planned_amount = $activity->planned_amount;
            $this->actual_amount = $activity->actual_amount;
            $this->percentage = $activity->percentage;
            $this->is_active = $activity->is_active;
            $this->is_planned = $activity->is_planned;
            $this->is_approved = $activity->is_approved;
            $this->expected_outcome = $activity->expected_outcome;
            $this->expected_outcome_date = $activity->expected_outcome_date;
            $this->activity_type = $activity->activity_type;
            $this->frequence_monitoring = $activity->frequence_monitoring;
            $this->source_id = $activity->source_id;
            $this->category_id = $activity->category_id;
            $this->financial_year_id = $activity->financial_year_id;

            // Load existing items
            $this->selectedItems = $activity->items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'name' => $item->item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })->toArray();

            // Load existing personnel
            $this->selectedPersonnel = $activity->personels->pluck('title_id')->toArray();
        } else {
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'activity_id', 'planned_activity', 'actual_activity', 'description',
            'status', 'planned_amount', 'actual_amount', 'percentage',
            'is_active', 'is_planned', 'is_approved', 'expected_outcome',
            'expected_outcome_date', 'activity_type', 'frequence_monitoring',
            'source_id', 'category_id'
        ]);

        $this->selectedItems = [];
        $this->selectedPersonnel = [];
        $this->status = 'pending';
        $this->is_active = true;
        $this->is_planned = true;
        $this->is_approved = false;
        $this->planned_amount = 0;
        $this->actual_amount = 0;
        $this->percentage = 0;

        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->financial_year_id = $currentFY->id;
        }
    }

    public function addItem($itemId, $itemName)
    {
        // Check if item already added
        $exists = collect($this->selectedItems)->firstWhere('item_id', $itemId);
        if (!$exists) {
            $this->selectedItems[] = [
                'item_id' => $itemId,
                'name' => $itemName,
                'quantity' => 1,
                'price' => 0,
            ];
        }
        $this->showItemSelector = false;
        $this->itemSearch = '';
        $this->calculateActualAmount();
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
        $this->calculateActualAmount();
    }

    public function updatedSelectedItems()
    {
        $this->calculateActualAmount();
    }

    public function calculateActualAmount()
    {
        $this->selectedItems = collect($this->selectedItems)->map(function ($item) {
            $item['quantity'] = $this->sanitizeAmount($item['quantity'] ?? 0);
            $item['price'] = $this->sanitizeAmount($item['price'] ?? 0);

            return $item;
        })->toArray();

        $this->actual_amount = collect($this->selectedItems)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });
    }

    public function updatedPlannedAmount($value)
    {
        $this->planned_amount = $this->sanitizeAmount($value);
    }

    protected function sanitizeAmount($value): float
    {
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return $clean === '' ? 0 : (float) $clean;
    }

    public function togglePersonnel($titleId)
    {
        if (in_array($titleId, $this->selectedPersonnel)) {
            $this->selectedPersonnel = array_diff($this->selectedPersonnel, [$titleId]);
        } else {
            $this->selectedPersonnel[] = $titleId;
        }
        $this->selectedPersonnel = array_values($this->selectedPersonnel);
    }

    public function save()
    {
        if ($this->modalMode === 'view') {
            return;
        }

        $this->planned_amount = $this->sanitizeAmount($this->planned_amount);

        $this->validate([
            'planned_activity' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'planned_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
            'source_id' => ['required', 'exists:sourceoffunds,id'],
            'category_id' => ['required', 'exists:chopcategoryareas,id'],
            'financial_year_id' => ['required', 'exists:financial_years,id'],
            'expected_outcome_date' => ['nullable', 'date'],
            'activity_type' => ['required', 'in:expenditure,revenue'],
            'frequence_monitoring' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () {
                $activityData = [
                    'planned_activity' => $this->planned_activity,
                    'actual_activity' => $this->actual_activity,
                    'description' => $this->description,
                    'status' => $this->status,
                    'planned_amount' => $this->planned_amount,
                    'actual_amount' => $this->actual_amount ?? 0,
                    'percentage' => $this->percentage ?? 0,
                    'is_active' => $this->is_active,
                    'is_planned' => $this->is_planned,
                    'is_approved' => $this->is_approved,
                    'expected_outcome' => $this->expected_outcome,
                    'expected_outcome_date' => $this->expected_outcome_date,
                    'activity_type' => $this->activity_type,
                    'frequence_monitoring' => $this->frequence_monitoring,
                    'source_id' => $this->source_id,
                    'category_id' => $this->category_id,
                    'financial_year_id' => $this->financial_year_id,
                ];

                if ($this->modalMode === 'edit' && $this->activity_id) {
                    $activity = chopactivities::findOrFail($this->activity_id);
                    $activity->update($activityData);

                    // Delete existing items and personnel
                    $activity->items()->delete();
                    $activity->personels()->delete();
                } else {
                    $activityData['added_by'] = Auth::id();
                    $activity = chopactivities::create($activityData);
                }

                // Add items
                foreach ($this->selectedItems as $item) {
                    activityitems::create([
                        'activity_id' => $activity->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'added_by' => Auth::id(),
                    ]);
                }

                // Add personnel
                foreach ($this->selectedPersonnel as $titleId) {
                    activitypersonel::create([
                        'activity_id' => $activity->id,
                        'title_id' => $titleId,
                        'added_by' => Auth::id(),
                    ]);
                }
            });
        } catch (\Throwable $th) {
            report($th);
            session()->flash('error', 'Failed to save activity. Please try again or contact support if the problem persists.');
            return;
        }

        session()->flash('success', $this->modalMode === 'edit' ? 'Activity updated successfully!' : 'Activity created successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $activity = chopactivities::findOrFail($id);
                $activity->items()->delete();
                $activity->personels()->delete();
                $activity->delete();
            });
        } catch (\Throwable $th) {
            report($th);
            session()->flash('error', 'Failed to delete activity. Please try again or contact support if the problem persists.');
            return;
        }

        session()->flash('success', 'Activity deleted successfully!');
    }

    public function render()
    {
        $activities = chopactivities::query()
            ->with(['source', 'category', 'financialYear', 'items', 'personels'])
            ->where(function ($query) {
                $query->where('planned_activity', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $sources = sourceoffunds::where('is_active', true)->orderBy('name')->get();
        $categories = chopcategoryarea::orderBy('name')->get();
        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();

        $availableItems = chopitems::query()
            ->where('is_active', true)
            ->when($this->itemSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->itemSearch . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        $jobTitles = Jobtitle::orderBy('name')->get();

        return view('livewire.chop.activities-management', [
            'activities' => $activities,
            'sources' => $sources,
            'categories' => $categories,
            'financialYears' => $financialYears,
            'availableItems' => $availableItems,
            'jobTitles' => $jobTitles,
            'activityTypes' => chopactivities::getActivityTypes(),
            'frequencyOptions' => chopactivities::getFrequencyOptions(),
        ]);
    }
}

<?php

namespace App\Livewire\Chop;

use App\Models\chopactivities;
use App\Models\chopcategoryarea;
use App\Models\FinancialYear;
use App\Models\sourceoffunds;
use Livewire\Component;
use Livewire\WithPagination;

class CostAnalysis extends Component
{
    use WithPagination;

    public $search = '';

    public $filterFinancialYear = '';

    public $filterCategory = '';

    public $filterActivityType = '';

    public $filterSource = '';

    public $expandedActivity = null;

    public function mount()
    {
        // Set default to current financial year
        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->filterFinancialYear = $currentFY->id;
        }
    }

    public function toggleActivity($activityId)
    {
        if ($this->expandedActivity === $activityId) {
            $this->expandedActivity = null;
        } else {
            $this->expandedActivity = $activityId;
        }
    }

    public function render()
    {
        // Get activities with calculated actual costs
        $activities = chopactivities::query()
            ->with(['category', 'financialYear', 'source', 'items.item'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('planned_activity', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('category', function ($cq) {
                            $cq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterActivityType, fn ($q) => $q->where('activity_type', $this->filterActivityType))
            ->when($this->filterSource, fn ($q) => $q->where('source_id', $this->filterSource))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate actual costs for each activity
        foreach ($activities as $activity) {
            $actualCost = $activity->items->sum(function ($item) {
                return ($item->quantity ?? 0) * ($item->price ?? 0);
            });
            $activity->calculated_actual_cost = $actualCost;
            $activity->variance = ($activity->planned_amount ?? 0) - $actualCost;
            $activity->variance_percentage = $activity->planned_amount > 0
                ? (($activity->variance / $activity->planned_amount) * 100)
                : 0;
        }

        // Calculate summary statistics
        $stats = $this->calculateStats($activities);

        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();
        $categories = chopcategoryarea::orderBy('name')->get();
        $activityTypes = chopactivities::getActivityTypes();
        $sources = sourceoffunds::orderBy('name')->get();

        return view('livewire.chop.cost-analysis', [
            'activities' => $activities,
            'financialYears' => $financialYears,
            'categories' => $categories,
            'activityTypes' => $activityTypes,
            'sources' => $sources,
            'stats' => $stats,
        ]);
    }

    private function calculateStats($activities)
    {
        $totalPlanned = 0;
        $totalActual = 0;

        foreach ($activities as $activity) {
            $totalPlanned += $activity->planned_amount ?? 0;
            $totalActual += $activity->calculated_actual_cost ?? 0;
        }

        $totalVariance = $totalPlanned - $totalActual;
        $variancePercentage = $totalPlanned > 0 ? (($totalVariance / $totalPlanned) * 100) : 0;

        return [
            'total_planned' => $totalPlanned,
            'total_actual' => $totalActual,
            'total_variance' => $totalVariance,
            'variance_percentage' => $variancePercentage,
            'total_activities' => $activities->total(),
        ];
    }
}

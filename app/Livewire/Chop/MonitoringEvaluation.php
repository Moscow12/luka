<?php

namespace App\Livewire\Chop;

use App\Models\ActivityReport;
use App\Models\chopactivities;
use App\Models\chopcategoryarea;
use App\Models\FinancialYear;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class MonitoringEvaluation extends Component
{
    use WithPagination;

    public $search = '';
    public $filterFinancialYear = '';
    public $filterCategory = '';
    public $selectedActivity = null;
    public $showActivityDetail = false;

    public function mount()
    {
        // Set default to current financial year
        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->filterFinancialYear = $currentFY->id;
        }
    }

    public function viewActivityDetail($activityId)
    {
        $this->selectedActivity = chopactivities::with(['category', 'source', 'financialYear', 'personels.title', 'items.item'])
            ->findOrFail($activityId);
        $this->showActivityDetail = true;
    }

    public function closeActivityDetail()
    {
        $this->showActivityDetail = false;
        $this->selectedActivity = null;
    }

    public function getMonthsFromFinancialYear($financialYear)
    {
        if (!$financialYear) {
            return [];
        }

        $startDate = Carbon::parse($financialYear->start_date);
        $endDate = Carbon::parse($financialYear->end_date);

        $months = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $months[] = [
                'name' => $current->format('M'),
                'full_name' => $current->format('F Y'),
                'number' => $current->month,
                'year' => $current->year,
            ];
            $current->addMonth();
        }

        return $months;
    }

    public function shouldMonitorInMonth($activity, $monthNumber)
    {
        if (!$activity->frequence_monitoring) {
            return false;
        }

        // Determine monitoring months based on frequency
        switch ($activity->frequence_monitoring) {
            case 'monthly':
                return true; // Monitor every month

            case 'quarterly':
                // Monitor every 3 months (Jan, Apr, Jul, Oct = months 1, 4, 7, 10)
                return in_array($monthNumber, [1, 4, 7, 10]);

            case 'bi_annually':
            case 'biannual':
                // Monitor twice a year (Jan and Jul = months 1, 7)
                return in_array($monthNumber, [1, 7]);

            case 'annually':
            case 'annual':
                // Monitor once a year (first month of financial year)
                $fyStartMonth = Carbon::parse($activity->financialYear->start_date)->month;
                return $monthNumber == $fyStartMonth;

            case 'weekly':
                // For weekly, show in all months
                return true;

            default:
                return false;
        }
    }

    public function getReportForActivityMonth($activityId, $monthYear)
    {
        return ActivityReport::where('activity_id', $activityId)
            ->where('report_month', $monthYear)
            ->first();
    }

    public function render()
    {
        // Get the selected financial year
        $selectedFY = null;
        if ($this->filterFinancialYear) {
            $selectedFY = FinancialYear::find($this->filterFinancialYear);
        }

        // Get months from the financial year
        $months = $this->getMonthsFromFinancialYear($selectedFY);

        // Get activities with filters
        $activities = chopactivities::query()
            ->with(['category', 'financialYear', 'source', 'personels.title', 'reports'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('planned_activity', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->where('is_active', true)
            ->orderBy('planned_activity')
            ->paginate(20);

        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();
        $categories = chopcategoryarea::orderBy('name')->get();

        // Calculate summary statistics
        $stats = [
            'total_activities' => $activities->total(),
            'total_planned_amount' => chopactivities::query()
                ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->where('is_active', true)
                ->sum('planned_amount'),
            'approved_activities' => chopactivities::query()
                ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->where('is_active', true)
                ->where('is_approved', true)
                ->count(),
            'pending_approval' => chopactivities::query()
                ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->where('is_active', true)
                ->where('is_approved', false)
                ->count(),
        ];

        return view('livewire.chop.monitoring-evaluation', [
            'activities' => $activities,
            'financialYears' => $financialYears,
            'categories' => $categories,
            'months' => $months,
            'selectedFY' => $selectedFY,
            'stats' => $stats,
            'frequencyOptions' => chopactivities::getFrequencyOptions(),
        ]);
    }
}

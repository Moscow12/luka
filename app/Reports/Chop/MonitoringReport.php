<?php

namespace App\Reports\Chop;

use App\Models\ActivityReport;
use App\Models\chopactivities;
use App\Models\chopcategoryarea;
use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * CHOP monitoring calendar: which months each active activity is due for monitoring
 * and what was reported. Shared by the Monitoring & Evaluation page, Excel and PDF.
 */
class MonitoringReport
{
    public const STATUS_LABELS = [
        'completed' => 'Completed',
        'partially_completed' => 'Partially completed',
        'not_completed' => 'Not completed',
        'cancelled' => 'Cancelled',
    ];

    /** Cell colours (hex without #) for Excel and PDF. */
    public const STATUS_COLOURS = [
        'completed' => '28A745',
        'partially_completed' => 'FFC107',
        'not_completed' => 'DC3545',
        'cancelled' => '6C757D',
        'pending' => 'E9ECEF',
    ];

    public const STATUS_SHORT = [
        'completed' => 'Done',
        'partially_completed' => 'Partial',
        'not_completed' => 'Not done',
        'cancelled' => 'Cancelled',
        'pending' => 'Pending',
    ];

    public function __construct(
        public ?string $search = null,
        public ?string $financialYearId = null,
        public ?string $categoryId = null,
    ) {
        $this->search = trim((string) $search) ?: null;
    }

    public static function fromArray(array $filters): self
    {
        return new self($filters['search'] ?? null, $filters['financial_year'] ?? null, $filters['category'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'financial_year' => $this->financialYearId,
            'category' => $this->categoryId,
        ]);
    }

    public function financialYear(): ?FinancialYear
    {
        return $this->financialYearId ? FinancialYear::find($this->financialYearId) : null;
    }

    public function query(): Builder
    {
        return chopactivities::query()
            ->with(['category', 'financialYear', 'source', 'personels.title', 'reports.reporter'])
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('planned_activity', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')))
            ->when($this->financialYearId, fn ($q) => $q->where('financial_year_id', $this->financialYearId))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->where('is_active', true)
            ->orderBy('planned_activity');
    }

    public function activities(): Collection
    {
        return $this->query()->get();
    }

    public function stats(): array
    {
        $base = chopactivities::query()
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('planned_activity', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')))
            ->when($this->financialYearId, fn ($q) => $q->where('financial_year_id', $this->financialYearId))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->where('is_active', true);

        return [
            'total_activities' => (clone $base)->count(),
            'total_planned_amount' => (float) (clone $base)->sum('planned_amount'),
            'approved_activities' => (clone $base)->where('is_approved', true)->count(),
            'pending_approval' => (clone $base)->where('is_approved', false)->count(),
        ];
    }

    /** Months of the financial year, in order. */
    public static function months(?FinancialYear $financialYear): array
    {
        if (! $financialYear) {
            return [];
        }

        $current = Carbon::parse($financialYear->start_date)->startOfMonth();
        $end = Carbon::parse($financialYear->end_date);
        $months = [];

        while ($current->lte($end)) {
            $months[] = [
                'name' => $current->format('M'),
                'full_name' => $current->format('F Y'),
                'number' => $current->month,
                'year' => $current->year,
                'key' => $current->format('Y-m'),
            ];
            $current->addMonth();
        }

        return $months;
    }

    public static function shouldMonitorInMonth($activity, int $monthNumber): bool
    {
        return match ($activity->frequence_monitoring) {
            'monthly', 'weekly' => true,
            'quarterly' => in_array($monthNumber, [1, 4, 7, 10]),
            'bi_annually', 'biannual' => in_array($monthNumber, [1, 7]),
            'annually', 'annual' => $activity->financialYear
                && $monthNumber === Carbon::parse($activity->financialYear->start_date)->month,
            default => false,
        };
    }

    /** The activity's report for a month (YYYY-MM), from the eager-loaded reports. */
    public static function reportFor($activity, string $monthKey): ?ActivityReport
    {
        return $activity->reports->firstWhere('report_month', $monthKey);
    }

    /**
     * One activity's state for a month: null (not due), 'pending' (due, no report)
     * or the report's status.
     */
    public static function cellStatus($activity, array $month): ?string
    {
        if (! self::shouldMonitorInMonth($activity, $month['number'])) {
            return null;
        }

        return self::reportFor($activity, $month['key'])?->status ?? 'pending';
    }

    public function filterSummary(): array
    {
        return [
            'Financial Year' => $this->financialYear()?->name ?? 'All',
            'Category' => $this->categoryId ? chopcategoryarea::find($this->categoryId)?->name : null,
            'Search' => $this->search,
        ];
    }

    /** Headings, rows and status colours for the Excel export. */
    public function excelTable(Collection $activities, array $months): array
    {
        $frequencies = chopactivities::getFrequencyOptions();
        $headings = ['#', 'Activity', 'Category', 'Source of Fund', 'Personnel', 'Budget (TZS)', 'Frequency', 'Approved'];
        $fixed = count($headings);
        foreach ($months as $month) {
            $headings[] = $month['name'].' '.$month['year'];
        }
        $headings[] = 'Reports';
        $headings[] = 'Amount Spent (TZS)';

        $rows = [];
        $fills = [];

        foreach ($activities->values() as $i => $activity) {
            $row = [
                $i + 1,
                $activity->planned_activity,
                $activity->category?->name,
                $activity->source?->name,
                $activity->personels->map(fn ($p) => $p->title?->name)->filter()->implode(', '),
                (float) ($activity->planned_amount ?? 0),
                $frequencies[$activity->frequence_monitoring] ?? '-',
                $activity->is_approved ? 'Yes' : 'No',
            ];

            foreach ($months as $m => $month) {
                $status = self::cellStatus($activity, $month);
                $row[] = $status ? self::STATUS_SHORT[$status] ?? ucfirst(str_replace('_', ' ', $status)) : '-';
                if ($status) {
                    $fills[$i][$fixed + $m] = self::STATUS_COLOURS[$status] ?? 'E9ECEF';
                }
            }

            $monthKeys = array_column($months, 'key');
            $reports = $months ? $activity->reports->whereIn('report_month', $monthKeys) : $activity->reports;
            $row[] = $reports->count();
            $row[] = (float) $reports->sum('amount_spent');

            $rows[] = $row;
        }

        return [$headings, $rows, $fills];
    }
}

<?php

namespace App\Reports\Chop;

use App\Models\chopactivities;
use App\Models\chopcategoryarea;
use App\Models\FinancialYear;
use App\Models\sourceoffunds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Planned vs actual cost per CHOP activity. One source of truth for the
 * Cost Analysis page, its Excel export and its PDF printout.
 */
class CostAnalysisReport
{
    public function __construct(
        public ?string $search = null,
        public ?string $financialYearId = null,
        public ?string $categoryId = null,
        public ?string $activityType = null,
        public ?string $sourceId = null,
    ) {
        $this->search = trim((string) $search) ?: null;
    }

    public static function fromArray(array $filters): self
    {
        return new self(
            $filters['search'] ?? null,
            $filters['financial_year'] ?? null,
            $filters['category'] ?? null,
            $filters['activity_type'] ?? null,
            $filters['source'] ?? null,
        );
    }

    /** Filters as query-string parameters, for print/PDF links. */
    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'financial_year' => $this->financialYearId,
            'category' => $this->categoryId,
            'activity_type' => $this->activityType,
            'source' => $this->sourceId,
        ]);
    }

    public function query(): Builder
    {
        return chopactivities::query()
            ->with(['category', 'financialYear', 'source', 'items.item'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('planned_activity', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->financialYearId, fn ($q) => $q->where('financial_year_id', $this->financialYearId))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->activityType, fn ($q) => $q->where('activity_type', $this->activityType))
            ->when($this->sourceId, fn ($q) => $q->where('source_id', $this->sourceId))
            ->orderBy('created_at', 'desc');
    }

    /** Adds calculated_actual_cost, variance and variance_percentage to each activity. */
    public static function withCosts(iterable $activities): iterable
    {
        foreach ($activities as $activity) {
            $actual = $activity->items->sum(fn ($item) => ($item->quantity ?? 0) * ($item->price ?? 0));
            $planned = (float) ($activity->planned_amount ?? 0);

            $activity->calculated_actual_cost = $actual;
            $activity->variance = $planned - $actual;
            $activity->variance_percentage = $planned > 0 ? ($activity->variance / $planned) * 100 : 0;
        }

        return $activities;
    }

    /** Every matching activity with its costs (not paginated). */
    public function activities(): Collection
    {
        return self::withCosts($this->query()->get());
    }

    /** Totals over all matching activities, not just the visible page. */
    public function stats(?Collection $activities = null): array
    {
        $activities ??= $this->activities();

        $planned = (float) $activities->sum(fn ($a) => $a->planned_amount ?? 0);
        $actual = (float) $activities->sum('calculated_actual_cost');
        $variance = $planned - $actual;

        return [
            'total_planned' => $planned,
            'total_actual' => $actual,
            'total_variance' => $variance,
            'variance_percentage' => $planned > 0 ? ($variance / $planned) * 100 : 0,
            'total_activities' => $activities->count(),
        ];
    }

    /** Human-readable filter line for report headers. */
    public function filterSummary(): array
    {
        return [
            'Financial Year' => $this->financialYearId ? FinancialYear::find($this->financialYearId)?->name : 'All',
            'Category' => $this->categoryId ? chopcategoryarea::find($this->categoryId)?->name : null,
            'Source of Fund' => $this->sourceId ? sourceoffunds::find($this->sourceId)?->name : null,
            'Type' => $this->activityType ? (chopactivities::getActivityTypes()[$this->activityType] ?? $this->activityType) : null,
            'Search' => $this->search,
        ];
    }

    /** Headings, rows and red/green variance shading for the Excel export. */
    public function excelTable(Collection $activities): array
    {
        $headings = ['#', 'Activity', 'Description', 'Category', 'Source of Fund', 'Financial Year', 'Type', 'Planned (TZS)', 'Actual (TZS)', 'Variance (TZS)', 'Variance %', 'Status'];
        $rows = [];
        $fills = [];

        foreach ($activities->values() as $i => $activity) {
            $under = $activity->variance >= 0;
            $rows[] = [
                $i + 1,
                $activity->planned_activity,
                $activity->description,
                $activity->category?->name,
                $activity->source?->name,
                $activity->financialYear?->name,
                ucfirst((string) $activity->activity_type),
                (float) ($activity->planned_amount ?? 0),
                (float) $activity->calculated_actual_cost,
                (float) $activity->variance,
                round($activity->variance_percentage, 1),
                $under ? 'Under budget' : 'Over budget',
            ];
            $fills[$i] = [9 => $under ? 'D1E7DD' : 'F8D7DA', 11 => $under ? 'D1E7DD' : 'F8D7DA'];
        }

        $stats = $this->stats($activities);
        $rows[] = ['', 'TOTAL', '', '', '', '', '', $stats['total_planned'], $stats['total_actual'], $stats['total_variance'], round($stats['variance_percentage'], 1), $stats['total_variance'] >= 0 ? 'Under budget' : 'Over budget'];

        return [$headings, $rows, $fills];
    }
}

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

    /** "COST ANALYSIS 2025-2026", as on the official template. */
    public function sheetTitle(): string
    {
        $year = $this->financialYearId ? FinancialYear::find($this->financialYearId)?->name : null;

        return 'COST ANALYSIS '.($year ? str_replace('/', '-', $year) : '(ALL FINANCIAL YEARS)');
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

    /**
     * The official CHOP cost analysis layout: objectives → targets → planned interventions,
     * each with its costed inputs and a subtotal per source of fund.
     *
     * Mapping (no dedicated fields exist yet): objective = category area,
     * target = the activity's expected outcome.
     *
     * @return array{objectives: array, by_source: array, grand_total: float}
     */
    public function sheet(Collection $activities): array
    {
        $objectives = [];
        $bySource = [];

        $grouped = $activities
            ->sortBy(fn ($a) => [
                mb_strtolower($a->category?->name ?? "\u{10FFFF}"),
                mb_strtolower(trim((string) $a->expected_outcome)),
                (string) $a->created_at,
            ])
            ->groupBy(fn ($a) => $a->category_id ?? 'none');

        foreach ($grouped->values() as $o => $objectiveActivities) {
            $targets = [];
            $interventionNumber = 0;

            foreach ($objectiveActivities->groupBy(fn ($a) => mb_strtolower(trim((string) $a->expected_outcome)))->values() as $t => $targetActivities) {
                $interventions = [];

                foreach ($targetActivities as $activity) {
                    $inputs = $activity->items->map(function ($line) {
                        $quantity = (float) ($line->quantity ?? 0);
                        $unitCost = (float) preg_replace('/[^0-9.]/', '', (string) ($line->price ?? '0'));

                        return [
                            'gfc_code' => $line->item?->gfc_code,
                            'name' => $line->item?->name ?? 'N/A',
                            'unit' => $line->item?->unit,
                            'unit_cost' => $unitCost,
                            'quantity' => $quantity,
                            'estimate' => $quantity * $unitCost,
                        ];
                    })->values()->all();

                    $total = (float) array_sum(array_column($inputs, 'estimate'));
                    $source = $activity->source?->name ?? 'Unspecified';
                    $bySource[$source] = [
                        'total' => ($bySource[$source]['total'] ?? 0) + $total,
                        'colour' => $activity->source?->colorcode,
                    ];

                    $interventions[] = [
                        'number' => ++$interventionNumber,
                        'name' => $activity->planned_activity,
                        'inputs' => $inputs,
                        'total' => $total,
                        'source' => $source,
                        'colour' => self::hexColour($activity->source?->colorcode),
                    ];
                }

                $targets[] = [
                    'number' => $t + 1,
                    'text' => trim((string) $targetActivities->first()->expected_outcome),
                    'interventions' => $interventions,
                ];
            }

            $objectives[] = [
                'number' => $o + 1,
                'name' => $objectiveActivities->first()->category?->name ?? 'Uncategorised',
                'targets' => $targets,
                'total' => (float) collect($targets)->flatMap(fn ($t) => $t['interventions'])->sum('total'),
            ];
        }

        ksort($bySource);

        return [
            'objectives' => $objectives,
            'by_source' => $bySource,
            'grand_total' => (float) array_sum(array_column($bySource, 'total')),
        ];
    }

    /** "#3366ff" / "36f" → "3366FF"; grey when missing or invalid. */
    public static function hexColour(?string $colour): string
    {
        $hex = ltrim(trim((string) $colour), '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? strtoupper($hex) : '6C757D';
    }

    /** Black or white text, whichever reads better on the colour. */
    public static function textOn(string $hex): string
    {
        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 150 ? '000000' : 'FFFFFF';
    }
}

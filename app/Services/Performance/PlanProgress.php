<?php

namespace App\Services\Performance;

use Carbon\CarbonInterface;

/**
 * The arithmetic behind the performance dashboard, kept free of database access so every
 * rule can be read (and tested) in one place.
 *
 * Progress is always a 0-100 percentage, or null when a goal has nothing measurable yet.
 */
class PlanProgress
{
    /** Points a goal may trail the calendar and still count as on track. */
    public const ON_TRACK_TOLERANCE = 10;

    /** Points behind the calendar after which an at-risk goal becomes behind. */
    public const AT_RISK_TOLERANCE = 25;

    /** Units that describe a rate rather than a count, so child achievements cannot be added up. */
    private const PERCENT_UNITS = ['%', 'percent', 'percentage', 'pct', 'rate'];

    public const STATUSES = [
        'complete' => ['label' => 'Complete', 'color' => 'success', 'icon' => 'fa-circle-check'],
        'on_track' => ['label' => 'On track', 'color' => 'success', 'icon' => 'fa-circle-arrow-up'],
        'at_risk' => ['label' => 'At risk', 'color' => 'warning', 'icon' => 'fa-triangle-exclamation'],
        'behind' => ['label' => 'Behind', 'color' => 'danger', 'icon' => 'fa-circle-exclamation'],
        'no_data' => ['label' => 'No target', 'color' => 'secondary', 'icon' => 'fa-circle-minus'],
    ];

    /** Rating bands applied to approved final evaluation scores, highest first. */
    public const RATING_BANDS = [
        ['min' => 90, 'label' => 'Outstanding'],
        ['min' => 75, 'label' => 'Very good'],
        ['min' => 60, 'label' => 'Good'],
        ['min' => 50, 'label' => 'Fair'],
        ['min' => 0, 'label' => 'Poor'],
    ];

    /** Achievement against a target, capped at 100. Null when there is no target to measure against. */
    public static function percent(?float $achieved, ?float $target): ?float
    {
        if (! $target || $target <= 0) {
            return null;
        }

        return round(min(max((float) $achieved, 0) / $target * 100, 100), 1);
    }

    /**
     * Weighted average of child progress. Rows are ['progress' => ?float, 'weight' => float].
     * Rows without progress are left out; if no row carries weight, every row counts equally.
     */
    public static function weighted(iterable $rows): ?float
    {
        $rows = collect($rows)->filter(fn ($row) => $row['progress'] !== null);

        if ($rows->isEmpty()) {
            return null;
        }

        $totalWeight = $rows->sum(fn ($row) => max((float) $row['weight'], 0));

        if ($totalWeight <= 0) {
            return round($rows->avg('progress'), 1);
        }

        return round($rows->sum(fn ($row) => $row['progress'] * max((float) $row['weight'], 0)) / $totalWeight, 1);
    }

    public static function isAdditive(?float $target, ?string $unit): bool
    {
        return $target > 0 && ! in_array(strtolower(trim((string) $unit)), self::PERCENT_UNITS, true);
    }

    /**
     * Progress of a goal that is delivered through child goals (organisation <- department <- employee).
     *
     * Count-like goals add up what the children achieved and compare it with the parent's own
     * target, so an under-cascaded target can never look complete. Rate-like goals, or children
     * whose achievement is unknown, fall back to the average of the children's progress.
     *
     * @param  array<int, array{achieved: ?float, progress: ?float, weight: float}>  $children
     * @return array{achieved: ?float, progress: ?float}
     */
    public static function rollUp(?float $target, ?string $unit, array $children): array
    {
        // A goal with a target that nobody is delivering is at zero, not unknown
        if ($children === []) {
            return $target > 0
                ? ['achieved' => static::isAdditive($target, $unit) ? 0.0 : null, 'progress' => 0.0]
                : ['achieved' => null, 'progress' => null];
        }

        $allKnown = collect($children)->every(fn ($child) => $child['achieved'] !== null);

        if (static::isAdditive($target, $unit) && $allKnown) {
            $achieved = collect($children)->sum('achieved');

            return ['achieved' => $achieved, 'progress' => static::percent($achieved, $target)];
        }

        return ['achieved' => null, 'progress' => static::weighted($children)];
    }

    /** Share of the plan period that has passed, 0-100. */
    public static function expected(?CarbonInterface $start, ?CarbonInterface $end, CarbonInterface $today): float
    {
        if (! $start || ! $end || $end->lte($start)) {
            return 0;
        }

        if ($today->lte($start)) {
            return 0;
        }

        if ($today->gte($end)) {
            return 100;
        }

        return round($start->diffInDays($today) / $start->diffInDays($end) * 100, 1);
    }

    public static function status(?float $progress, float $expected): string
    {
        return match (true) {
            $progress === null => 'no_data',
            $progress >= 100 => 'complete',
            $progress >= $expected - self::ON_TRACK_TOLERANCE => 'on_track',
            $progress >= $expected - self::AT_RISK_TOLERANCE => 'at_risk',
            default => 'behind',
        };
    }

    public static function rating(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        foreach (self::RATING_BANDS as $band) {
            if ($score >= $band['min']) {
                return $band['label'];
            }
        }

        return null;
    }

    /**
     * SMART test for one goal.
     *  S  Specific   - it is described, not just named
     *  M  Measurable - it has a numeric target and a unit
     *  A  Achievable - the target sits inside its own acceptable range
     *  R  Relevant   - it is linked to a goal one level up
     *  T  Time-bound - it belongs to a plan with a defined period
     *
     * @return array{S: bool, M: bool, A: bool, R: bool, T: bool}
     */
    public static function smart(object $goal, bool $linkedToParent, bool $timeBound): array
    {
        $target = $goal->target_value !== null ? (float) $goal->target_value : null;

        return [
            'S' => trim((string) $goal->description) !== '',
            'M' => $target > 0 && trim((string) $goal->target_unit) !== '',
            'A' => $target > 0
                && ($goal->min_acceptable === null || (float) $goal->min_acceptable <= $target)
                && ($goal->max_possible === null || (float) $goal->max_possible >= $target),
            'R' => $linkedToParent,
            'T' => $timeBound,
        ];
    }
}

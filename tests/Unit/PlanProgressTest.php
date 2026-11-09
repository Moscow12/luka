<?php

use App\Services\Performance\PlanProgress;
use Carbon\Carbon;

it('measures achievement against a target and caps it at 100', function () {
    expect(PlanProgress::percent(25, 50))->toBe(50.0)
        ->and(PlanProgress::percent(80, 50))->toBe(100.0)
        ->and(PlanProgress::percent(10, null))->toBeNull()
        ->and(PlanProgress::percent(10, 0))->toBeNull();
});

it('averages by weight and skips goals with nothing to measure', function () {
    $rows = [
        ['progress' => 100.0, 'weight' => 75],
        ['progress' => 0.0, 'weight' => 25],
        ['progress' => null, 'weight' => 50],
    ];

    expect(PlanProgress::weighted($rows))->toBe(75.0)
        ->and(PlanProgress::weighted([['progress' => 40.0, 'weight' => 0], ['progress' => 60.0, 'weight' => 0]]))->toBe(50.0)
        ->and(PlanProgress::weighted([]))->toBeNull();
});

it('adds up child achievements for counted targets so an under-assigned target cannot look complete', function () {
    // Both children finished their own share, but only 60 of the 100 were ever assigned
    $children = [
        ['achieved' => 30.0, 'progress' => 100.0, 'weight' => 50],
        ['achieved' => 30.0, 'progress' => 100.0, 'weight' => 50],
    ];

    expect(PlanProgress::rollUp(100, 'clients', $children))->toBe(['achieved' => 60.0, 'progress' => 60.0]);
});

it('averages child progress for percentage targets', function () {
    $children = [
        ['achieved' => 95.0, 'progress' => 100.0, 'weight' => 1],
        ['achieved' => 45.0, 'progress' => 50.0, 'weight' => 1],
    ];

    expect(PlanProgress::rollUp(90, '%', $children))->toBe(['achieved' => null, 'progress' => 75.0]);
});

it('treats a targeted goal with nobody delivering it as zero', function () {
    expect(PlanProgress::rollUp(100, 'clients', []))->toBe(['achieved' => 0.0, 'progress' => 0.0])
        ->and(PlanProgress::rollUp(null, null, []))->toBe(['achieved' => null, 'progress' => null]);
});

it('measures time elapsed across the plan period', function () {
    $start = Carbon::parse('2026-01-01');
    $end = Carbon::parse('2026-01-11');

    expect(PlanProgress::expected($start, $end, Carbon::parse('2025-12-01')))->toEqual(0)
        ->and(PlanProgress::expected($start, $end, Carbon::parse('2026-01-06')))->toEqual(50)
        ->and(PlanProgress::expected($start, $end, Carbon::parse('2026-02-01')))->toEqual(100);
});

it('classifies progress against the calendar', function () {
    expect(PlanProgress::status(100, 50))->toBe('complete')
        ->and(PlanProgress::status(42, 50))->toBe('on_track')
        ->and(PlanProgress::status(30, 50))->toBe('at_risk')
        ->and(PlanProgress::status(20, 50))->toBe('behind')
        ->and(PlanProgress::status(null, 50))->toBe('no_data');
});

it('checks each SMART criterion', function () {
    $good = (object) ['description' => 'Serve clients', 'target_value' => 10, 'target_unit' => 'clients', 'min_acceptable' => 5, 'max_possible' => 15];
    $vague = (object) ['description' => '', 'target_value' => null, 'target_unit' => null, 'min_acceptable' => null, 'max_possible' => null];
    $impossible = (object) ['description' => 'x', 'target_value' => 20, 'target_unit' => 'clients', 'min_acceptable' => null, 'max_possible' => 15];

    expect(PlanProgress::smart($good, true, true))->toBe(['S' => true, 'M' => true, 'A' => true, 'R' => true, 'T' => true])
        ->and(PlanProgress::smart($vague, false, true))->toBe(['S' => false, 'M' => false, 'A' => false, 'R' => false, 'T' => true])
        ->and(PlanProgress::smart($impossible, true, true)['A'])->toBeFalse();
});

it('maps final scores to rating bands', function () {
    expect(PlanProgress::rating(92))->toBe('Outstanding')
        ->and(PlanProgress::rating(60))->toBe('Good')
        ->and(PlanProgress::rating(10))->toBe('Poor')
        ->and(PlanProgress::rating(null))->toBeNull();
});

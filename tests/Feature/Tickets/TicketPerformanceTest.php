<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\Tickets\TicketActionException;
use App\Services\Tickets\TicketMetricsService;
use App\Services\Tickets\TicketService;
use Tests\Support\TicketTestCase;

uses(TicketTestCase::class);

beforeEach(function () {
    $this->service = app(TicketService::class);
    $this->dept = $this->makeDepartment('ICT');
    $this->category = $this->makeCategory();
    $this->creator = $this->makeStaff($this->dept, ['create-tickets']);
    $this->assignee = $this->makeStaff($this->dept);
    $this->employee = $this->employeeOf($this->assignee);

    $this->newTicket = function (array $overrides = []) {
        return $this->service->create(array_merge([
            'subject' => 'Task',
            'description' => '<p>Do it</p>',
            'category_id' => $this->category->id,
            'priority' => 'medium',
            'department_id' => $this->dept->id,
            'assignee_employee_id' => $this->employee->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'visibility' => 'participants',
        ], $overrides), $this->creator);
    };

    // Accept → work → resolve → close with a rating
    $this->finish = function (Ticket $ticket, int $rating = 5) {
        foreach ([TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved] as $s) {
            $ticket = $this->service->transition($ticket, $s, $this->assignee);
        }

        return $this->service->close($ticket, $this->creator, $rating);
    };
});

it('only links active duties of the assignee', function () {
    $otherDuty = $this->makeDuty($this->employeeOf($this->creator));

    ($this->newTicket)(['employee_assigned_duty_id' => $otherDuty->id]);
})->throws(TicketActionException::class);

it('counts closed tickets towards a numeric duty', function () {
    $duty = $this->makeDuty($this->employee, ['measurement_type' => 'numeric', 'target_value' => 2]);

    $a = ($this->newTicket)(['employee_assigned_duty_id' => $duty->id]);
    $b = ($this->newTicket)(['employee_assigned_duty_id' => $duty->id]);

    ($this->finish)($a);
    $duty->refresh();
    expect((float) $duty->actual_achievement)->toBe(1.0)
        ->and((float) $duty->score)->toBe(50.0)
        ->and($duty->status)->toBe('in_progress');

    ($this->finish)($b);
    $duty->refresh();
    expect((float) $duty->score)->toBe(100.0)
        ->and($duty->status)->toBe('completed');
});

it('scores a percentage duty by on-time closure', function () {
    $duty = $this->makeDuty($this->employee, ['measurement_type' => 'percentage', 'target_value' => 100]);

    $onTime = ($this->newTicket)(['employee_assigned_duty_id' => $duty->id]);
    $late = ($this->newTicket)(['employee_assigned_duty_id' => $duty->id]);

    ($this->finish)($onTime);
    $this->travel(10)->days();
    ($this->finish)($late);

    $duty->refresh();
    expect((float) $duty->actual_achievement)->toBe(50.0)
        ->and((float) $duty->score)->toBe(50.0)
        ->and($duty->status)->toBe('completed');
});

it('scores a rating-scale duty from quality ratings', function () {
    $duty = $this->makeDuty($this->employee, ['measurement_type' => 'rating_scale', 'rating_scale_max' => 10, 'target_value' => null]);

    ($this->finish)(($this->newTicket)(['employee_assigned_duty_id' => $duty->id]), 4);
    ($this->finish)(($this->newTicket)(['employee_assigned_duty_id' => $duty->id]), 3);

    $duty->refresh();
    expect((float) $duty->actual_achievement)->toBe(7.0)   // avg 3.5 / 5 * 10
        ->and((float) $duty->score)->toBe(70.0);
});

it('leaves reviewed duties untouched', function () {
    $duty = $this->makeDuty($this->employee, ['reviewed_at' => now(), 'status' => 'in_progress']);
    // Linking validates the duty is still open, so attach directly
    $ticket = ($this->newTicket)();
    $ticket->update(['employee_assigned_duty_id' => $duty->id]);

    ($this->finish)($ticket->fresh());

    expect($duty->fresh()->actual_achievement)->toBeNull();
});

it('computes PRD metrics and the composite scorecard', function () {
    $closedOnTime = ($this->finish)(($this->newTicket)(), 5);

    // Resolved, reopened, resolved again and closed late
    $reworked = ($this->newTicket)();
    foreach ([TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved] as $s) {
        $reworked = $this->service->transition($reworked, $s, $this->assignee);
    }
    $reworked = $this->service->reopen($reworked, $this->creator, 'Incomplete');
    $this->travel(5)->days();
    $reworked = $this->service->transition($reworked, TicketStatus::Resolved, $this->assignee);
    $this->service->close($reworked, $this->creator, 3);

    // Still open and now overdue
    $this->travel(-5)->days();
    ($this->newTicket)(['due_date' => now()->addDay()->toDateString()]);
    $this->travel(5)->days();

    $metrics = app(TicketMetricsService::class);
    $summary = $metrics->summary();

    expect($summary['total'])->toBe(3)
        ->and($summary['active'])->toBe(1)
        ->and($summary['overdue'])->toBe(1)
        ->and($summary['reopen_rate'])->toBe(50.0)      // 1 reopened / 2 ever resolved
        ->and($summary['art_hours'])->not->toBeNull();

    $card = $metrics->scorecardForEmployee($this->employee);
    // on-time 1/2, avg rating 4/5, reopen 50% → 40*.5 + 30*.8 + 30*.5 = 59
    expect($card['closed'])->toBe(2)
        ->and($card['on_time_rate'])->toBe(50.0)
        ->and($card['avg_rating'])->toBe(4.0)
        ->and($card['score'])->toBe(59.0);

    $workload = $metrics->workload();
    expect($workload['assignees']->first()['total'])->toBe(3);
});

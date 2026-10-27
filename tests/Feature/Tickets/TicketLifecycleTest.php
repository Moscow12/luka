<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketAssignmentLog;
use App\Models\TicketMessage;
use App\Models\TicketStatusLog;
use App\Services\Tickets\TicketAccess;
use App\Services\Tickets\TicketActionException;
use App\Services\Tickets\TicketService;
use Tests\Support\TicketTestCase;

uses(TicketTestCase::class);

beforeEach(function () {
    $this->service = app(TicketService::class);
    $this->dept = $this->makeDepartment('ICT');
    $this->otherDept = $this->makeDepartment('Finance');
    $this->category = $this->makeCategory();

    $this->creator = $this->makeStaff($this->otherDept, ['create-tickets']);
    $this->assignee = $this->makeStaff($this->dept);
    $this->colleague = $this->makeStaff($this->dept);
    $this->supervisor = $this->makeStaff($this->dept, ['manage-department-tickets']);
    $this->outsider = $this->makeStaff($this->otherDept);

    $this->newTicket = function (array $overrides = []) {
        return $this->service->create(array_merge([
            'subject' => 'Printer on 2nd floor is down',
            'description' => '<p>Please fix <script>alert(1)</script><strong>today</strong></p>',
            'category_id' => $this->category->id,
            'priority' => 'high',
            'department_id' => $this->dept->id,
            'assignee_employee_id' => $this->employeeOf($this->assignee)->id,
            'due_date' => now()->addDays(2)->toDateString(),
            'visibility' => 'participants',
        ], $overrides), $this->creator);
    };
});

it('numbers tickets sequentially per year in HRP-YYYY-NNNNN format', function () {
    $first = ($this->newTicket)();
    $second = ($this->newTicket)();

    $year = now()->format('Y');
    expect($first->ticket_number)->toBe("HRP-{$year}-00001")
        ->and($second->ticket_number)->toBe("HRP-{$year}-00002");

    $this->travelTo(now()->addYear()->startOfYear());
    expect(($this->newTicket)()->ticket_number)->toBe('HRP-'.now()->format('Y').'-00001');
});

it('sanitises rich text and records creation metadata', function () {
    $ticket = ($this->newTicket)();

    expect($ticket->description)->not->toContain('<script>')
        ->and($ticket->description)->toContain('<strong>today</strong>')
        ->and($ticket->created_by)->toBe($this->creator->id)
        ->and($ticket->status)->toBe(TicketStatus::Assigned);
});

it('routes a ticket without assignee to the department queue as Open', function () {
    $ticket = ($this->newTicket)(['assignee_employee_id' => null]);

    expect($ticket->status)->toBe(TicketStatus::Open)
        ->and($ticket->assignee_employee_id)->toBeNull();

    $ticket = $this->service->assign($ticket, $this->dept->id, $this->employeeOf($this->assignee)->id, $this->supervisor, 'Delegating');
    expect($ticket->status)->toBe(TicketStatus::Assigned);
});

it('runs the full happy path with the right actors', function () {
    $ticket = ($this->newTicket)();

    $ticket = $this->service->transition($ticket, TicketStatus::Accepted, $this->assignee);
    $ticket = $this->service->transition($ticket, TicketStatus::InProgress, $this->assignee);
    $ticket = $this->service->transition($ticket, TicketStatus::PendingResponse, $this->creator);
    $ticket = $this->service->transition($ticket, TicketStatus::InProgress, $this->assignee);
    $ticket = $this->service->transition($ticket, TicketStatus::Resolved, $this->assignee);
    $ticket = $this->service->close($ticket, $this->creator, 4, 'Good job');

    expect($ticket->status)->toBe(TicketStatus::Closed)
        ->and($ticket->quality_rating)->toBe(4)
        ->and($ticket->accepted_at)->not->toBeNull()
        ->and($ticket->resolved_at)->not->toBeNull()
        ->and($ticket->closed_by)->toBe($this->creator->id);

    expect(TicketStatusLog::where('ticket_id', $ticket->id)->pluck('to_status')->all())
        ->toBe(['open', 'assigned', 'accepted', 'in_progress', 'pending_response', 'in_progress', 'resolved', 'closed']);
});

it('refuses actions by the wrong actor', function (string $who, TicketStatus $target) {
    $ticket = ($this->newTicket)();
    $this->service->transition($ticket, TicketStatus::Accepted, $this->assignee);

    $this->service->transition($ticket->fresh(), $target, $this->{$who});
})->with([
    'creator cannot start work' => ['creator', TicketStatus::InProgress],
    'supervisor cannot resolve' => ['supervisor', TicketStatus::Resolved],
    'outsider cannot cancel' => ['outsider', TicketStatus::Cancelled],
])->throws(TicketActionException::class);

it('refuses illegal transitions', function () {
    $ticket = ($this->newTicket)();

    $this->service->transition($ticket, TicketStatus::Resolved, $this->assignee);
})->throws(TicketActionException::class);

it('does not let the assignee close their own work', function () {
    $ticket = ($this->newTicket)();
    foreach ([TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved] as $s) {
        $ticket = $this->service->transition($ticket, $s, $this->assignee);
    }

    $this->service->close($ticket, $this->assignee, 5);
})->throws(TicketActionException::class);

it('reopens a resolved ticket and counts it', function () {
    $ticket = ($this->newTicket)();
    foreach ([TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved] as $s) {
        $ticket = $this->service->transition($ticket, $s, $this->assignee);
    }

    $ticket = $this->service->reopen($ticket, $this->creator, 'Still broken');

    expect($ticket->status)->toBe(TicketStatus::Reopened)
        ->and($ticket->reopen_count)->toBe(1)
        ->and($ticket->resolved_at)->toBeNull();

    $ticket = $this->service->transition($ticket, TicketStatus::InProgress, $this->assignee);
    expect($ticket->status)->toBe(TicketStatus::InProgress);
});

it('keeps exactly one primary assignee and logs every reassignment', function () {
    $ticket = ($this->newTicket)();
    $ticket = $this->service->assign($ticket, $this->dept->id, $this->employeeOf($this->colleague)->id, $this->supervisor, 'Workload balancing');

    expect($ticket->assignee_employee_id)->toBe($this->employeeOf($this->colleague)->id)
        ->and($ticket->participants()->where('role', 'assignee')->count())->toBe(1)
        ->and($ticket->participants()->where('user_id', $this->assignee->id)->value('role'))->toBe('collaborator');

    $last = TicketAssignmentLog::where('ticket_id', $ticket->id)->latest('created_at')->get()->last();
    expect(TicketAssignmentLog::where('ticket_id', $ticket->id)->count())->toBe(2)
        ->and($last->previous_employee_id)->toBe($this->employeeOf($this->assignee)->id)
        ->and($last->new_employee_id)->toBe($this->employeeOf($this->colleague)->id)
        ->and($last->acted_by)->toBe($this->supervisor->id);

    // The old assignee can no longer act as assignee
    $this->service->transition($ticket, TicketStatus::Accepted, $this->assignee);
})->throws(TicketActionException::class);

it('rejects an assignee from another department', function () {
    ($this->newTicket)(['assignee_employee_id' => $this->employeeOf($this->outsider)->id]);
})->throws(TicketActionException::class);

it('makes the history logs immutable', function () {
    $ticket = ($this->newTicket)();
    $log = TicketStatusLog::where('ticket_id', $ticket->id)->first();

    expect(fn () => $log->update(['remarks' => 'tampered']))->toThrow(LogicException::class)
        ->and(fn () => $log->delete())->toThrow(LogicException::class)
        ->and(fn () => TicketAssignmentLog::where('ticket_id', $ticket->id)->first()->delete())->toThrow(LogicException::class);
});

it('enforces visibility', function () {
    $ticket = ($this->newTicket)();

    expect(TicketAccess::canView($this->creator, $ticket))->toBeTrue()
        ->and(TicketAccess::canView($this->assignee, $ticket))->toBeTrue()
        ->and(TicketAccess::canView($this->supervisor, $ticket))->toBeTrue()
        ->and(TicketAccess::canView($this->colleague, $ticket))->toBeFalse()
        ->and(TicketAccess::canView($this->outsider, $ticket))->toBeFalse();

    $ticket->update(['visibility' => 'department']);
    expect(TicketAccess::canView($this->colleague, $ticket->fresh()))->toBeTrue();

    $visibleToColleague = TicketAccess::scopeVisible(Ticket::query(), $this->colleague)->pluck('id')->all();
    expect($visibleToColleague)->toBe([$ticket->id]);
    expect(TicketAccess::scopeVisible(Ticket::query(), $this->outsider)->count())->toBe(0);
});

it('hides private notes from the creator', function () {
    $ticket = ($this->newTicket)();

    $this->service->postMessage($ticket, $this->assignee, '<p>internal: vendor call needed</p>', 'private_note');
    $this->service->postMessage($ticket, $this->assignee, '<p>Working on it</p>');

    expect(TicketAccess::canViewPrivateNotes($this->creator, $ticket))->toBeFalse()
        ->and(TicketAccess::canViewPrivateNotes($this->supervisor, $ticket))->toBeTrue()
        ->and(TicketAccess::canViewPrivateNotes($this->assignee, $ticket))->toBeTrue();

    expect(fn () => $this->service->postMessage($ticket, $this->creator, 'sneaky', 'private_note'))
        ->toThrow(TicketActionException::class);

    // Bodies are encrypted at rest
    $raw = \DB::table('ticket_messages')->where('ticket_id', $ticket->id)->pluck('body')->all();
    foreach ($raw as $body) {
        expect($body)->not->toContain('Working on it');
    }
    expect(TicketMessage::where('ticket_id', $ticket->id)->where('type', 'public')->first()->body)->toContain('Working on it');
});

it('treats the department supervisor title holder as supervisor', function () {
    $title = \App\Models\Jobtitle::forceCreate(['id' => (string) \Str::uuid(), 'name' => 'Head of ICT']);
    $this->dept->update(['supervisor_title_id' => $title->id]);
    $head = $this->makeStaff($this->otherDept, [], ['title_id' => $title->id]);

    $ticket = ($this->newTicket)();

    expect(TicketAccess::isSupervisorOf($head, $ticket))->toBeTrue()
        ->and(TicketAccess::canManage($head, $ticket))->toBeTrue();
});

<?php

use App\Enums\TicketStatus;
use App\Livewire\Common\Concerns\BuildsNotifications;
use App\Services\Tickets\TicketService;
use Tests\Support\TicketTestCase;

uses(TicketTestCase::class);

beforeEach(function () {
    // BuildsNotifications also reads leave/payroll tables that the slim test schema lacks; only tickets matter here
    $this->ticketKeysFor = function ($user) {
        $this->actingAs($user);
        $builder = new class
        {
            use BuildsNotifications;

            public function run($user)
            {
                return $this->buildTicketNotifications($user, \App\Models\Employee::where('user_id', $user->id)->first(), [], 10);
            }
        };

        return collect($builder->run($user))->pluck('key')->map(fn ($k) => explode(':', $k)[2])->all();
    };

    $this->service = app(TicketService::class);
    $this->dept = $this->makeDepartment('ICT');
    $this->category = $this->makeCategory();
    $this->creator = $this->makeStaff($this->makeDepartment('HR'), ['create-tickets']);
    $this->assignee = $this->makeStaff($this->dept);
    $this->supervisor = $this->makeStaff($this->dept, ['manage-department-tickets']);

    $this->newTicket = fn (?string $assigneeId) => $this->service->create([
        'subject' => 'Notify me', 'description' => '<p>x</p>', 'category_id' => $this->category->id,
        'priority' => 'medium', 'department_id' => $this->dept->id, 'assignee_employee_id' => $assigneeId,
        'due_date' => now()->addDay()->toDateString(), 'visibility' => 'participants',
    ], $this->creator);
});

it('tells department supervisors about unassigned queue tickets', function () {
    ($this->newTicket)(null);

    expect(($this->ticketKeysFor)($this->supervisor))->toContain('queue')
        ->and(($this->ticketKeysFor)($this->assignee))->toBe([]);
});

it('tells the assignee about a new assignment and the creator about a resolution', function () {
    $ticket = ($this->newTicket)($this->employeeOf($this->assignee)->id);

    expect(($this->ticketKeysFor)($this->assignee))->toContain('assigned')
        ->and(($this->ticketKeysFor)($this->supervisor))->not->toContain('queue');

    foreach ([TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved] as $s) {
        $ticket = $this->service->transition($ticket, $s, $this->assignee);
    }
    $this->service->postMessage($ticket, $this->assignee, '<p>Done, please check</p>');

    $creatorKeys = ($this->ticketKeysFor)($this->creator);
    expect($creatorKeys)->toContain('resolved')->toContain('message');
});

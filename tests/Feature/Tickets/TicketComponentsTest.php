<?php

use App\Enums\TicketStatus;
use App\Livewire\Setup\TicketCategories;
use App\Livewire\Tickets\CreateTicket;
use App\Livewire\Tickets\TicketDashboard;
use App\Livewire\Tickets\TicketList;
use App\Livewire\Tickets\TicketView;
use App\Models\Ticket;
use App\Services\Tickets\TicketService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\TicketTestCase;

uses(TicketTestCase::class);

beforeEach(function () {
    Storage::fake('local');

    $this->dept = $this->makeDepartment('ICT');
    $this->category = $this->makeCategory();
    $this->creator = $this->makeStaff($this->dept, ['create-tickets']);
    $this->assignee = $this->makeStaff($this->dept);
    $this->supervisor = $this->makeStaff($this->dept, ['manage-department-tickets']);
    $this->admin = $this->makeStaff($this->dept, ['view-all-tickets', 'view-ticket-reports', 'manage-ticket-categories']);
});

it('creates and assigns a ticket with attachments from one form', function () {
    $duty = $this->makeDuty($this->employeeOf($this->assignee));

    Livewire::actingAs($this->creator)
        ->test(CreateTicket::class)
        ->set('subject', 'Install accounting software')
        ->set('description', '<p>On the new laptop</p>')
        ->set('category_id', $this->category->id)
        ->set('department_id', $this->dept->id)
        ->set('assignee_employee_id', $this->employeeOf($this->assignee)->id)
        ->assertSee('Handle support tickets')
        ->set('employee_assigned_duty_id', $duty->id)
        ->set('priority', 'high')
        ->set('attachments', [UploadedFile::fake()->create('quote.pdf', 300, 'application/pdf')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::firstOrFail();
    expect($ticket->status)->toBe(TicketStatus::Assigned)
        ->and($ticket->employee_assigned_duty_id)->toBe($duty->id)
        ->and($ticket->attachments)->toHaveCount(1);

    // Stored encrypted, served decrypted to someone allowed to view
    $attachment = $ticket->attachments->first();
    expect(Storage::disk('local')->get($attachment->path))->not->toBe(UploadedFile::fake()->create('x.pdf', 1)->getContent());
    $this->actingAs($this->assignee)->get(route('tickets.attachment', $attachment))->assertOk();
    $this->actingAs($this->makeStaff($this->makeDepartment('Other')))->get(route('tickets.attachment', $attachment))->assertForbidden();
});

it('validates the due date and required fields', function () {
    Livewire::actingAs($this->creator)
        ->test(CreateTicket::class)
        ->set('due_date', now()->subDay()->toDateString())
        ->call('save')
        ->assertHasErrors(['subject', 'description', 'category_id', 'department_id', 'due_date']);
});

it('blocks ticket creation without permission', function () {
    Livewire::actingAs($this->assignee)->test(CreateTicket::class)->assertForbidden();
});

it('shows the right tickets in each list scope', function () {
    $service = app(TicketService::class);
    $ticket = $service->create([
        'subject' => 'Scoped ticket', 'description' => '<p>x</p>', 'category_id' => $this->category->id,
        'priority' => 'low', 'department_id' => $this->dept->id, 'assignee_employee_id' => $this->employeeOf($this->assignee)->id,
        'due_date' => now()->addDay()->toDateString(), 'visibility' => 'participants',
    ], $this->creator);

    Livewire::actingAs($this->creator)->test(TicketList::class, ['scope' => 'mine'])->assertSee('Scoped ticket');
    Livewire::actingAs($this->assignee)->test(TicketList::class, ['scope' => 'assigned'])->assertSee('Scoped ticket')->assertSee('New / Unaccepted');
    Livewire::actingAs($this->assignee)->test(TicketList::class, ['scope' => 'mine'])->assertDontSee('Scoped ticket');
    Livewire::actingAs($this->supervisor)->test(TicketList::class, ['scope' => 'department'])->assertSee('Scoped ticket')->assertSee('Workload by officer');
    Livewire::actingAs($this->admin)->test(TicketList::class, ['scope' => 'all'])->assertSee('Scoped ticket');

    Livewire::actingAs($this->assignee)->test(TicketList::class, ['scope' => 'department'])->assertForbidden();
    Livewire::actingAs($this->assignee)->test(TicketList::class, ['scope' => 'all'])->assertForbidden();
});

it('drives the lifecycle and conversation from the ticket page', function () {
    $service = app(TicketService::class);
    $ticket = $service->create([
        'subject' => 'Page ticket', 'description' => '<p>x</p>', 'category_id' => $this->category->id,
        'priority' => 'low', 'department_id' => $this->dept->id, 'assignee_employee_id' => $this->employeeOf($this->assignee)->id,
        'due_date' => now()->addDay()->toDateString(), 'visibility' => 'participants',
    ], $this->creator);

    Livewire::actingAs($this->assignee)
        ->test(TicketView::class, ['ticket' => $ticket])
        ->assertSee('Accept')
        ->call('quickTransition', 'accepted')
        ->call('quickTransition', 'in_progress')
        ->set('isPrivate', true)
        ->set('newMessage', '<p>Secret vendor detail</p>')
        ->call('sendMessage')
        ->set('newMessage', '<p>Public update</p>')
        ->call('sendMessage')
        ->call('quickTransition', 'resolved')
        ->assertSet('modal', 'status')
        ->set('remarks', 'Installed and tested')
        ->call('submitStatus');

    expect($ticket->fresh()->status)->toBe(TicketStatus::Resolved);

    Livewire::actingAs($this->creator)
        ->test(TicketView::class, ['ticket' => $ticket])
        ->assertSee('Public update')
        ->assertDontSee('Secret vendor detail')
        ->assertSee('Verify &amp; close', false)
        ->call('openModal', 'close')
        ->set('rating', 4)
        ->call('submitClose');

    expect($ticket->fresh()->status)->toBe(TicketStatus::Closed)
        ->and($ticket->fresh()->quality_rating)->toBe(4);

    Livewire::actingAs($this->supervisor)
        ->test(TicketView::class, ['ticket' => $ticket])
        ->assertSee('Secret vendor detail');

    Livewire::actingAs($this->makeStaff($this->makeDepartment('Other')))
        ->test(TicketView::class, ['ticket' => $ticket])
        ->assertForbidden();
});

it('renders the analytics dashboard and category setup for authorised users', function () {
    Livewire::actingAs($this->admin)->test(TicketDashboard::class)->assertSee('Reopen defect rate');
    Livewire::actingAs($this->assignee)->test(TicketDashboard::class)->assertForbidden();

    Livewire::actingAs($this->admin)
        ->test(TicketCategories::class)
        ->call('openModal')
        ->set('name', 'Payroll')
        ->set('default_sla_hours', 48)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Payroll');
});

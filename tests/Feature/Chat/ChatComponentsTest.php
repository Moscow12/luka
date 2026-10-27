<?php

use App\Livewire\Chat\ChatAnalytics;
use App\Livewire\Chat\ConversationPane;
use App\Livewire\Chat\Launcher;
use App\Livewire\Chat\Workspace;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\ChatService;
use App\Services\Tickets\TicketService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\ChatTestCase;

uses(ChatTestCase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->chat = app(ChatService::class);
    $this->dept = $this->makeDepartment('ICT');
    $this->alice = $this->makeStaff($this->dept, [], ['first_name' => 'Alice', 'last_name' => 'Green']);
    $this->bob = $this->makeStaff($this->dept, [], ['first_name' => 'Bob', 'last_name' => 'Vance']);
    $this->outsider = $this->makeStaff($this->makeDepartment('Finance'));
});

it('opens the workspace with and without a conversation in the URL', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);

    // Mounted the way the router does for /chat (no parameter) — this used to 404
    Livewire::actingAs($this->alice)->test(Workspace::class)->assertOk()->assertSee('Welcome to Workspace Chat');
    Livewire::actingAs($this->alice)->test(Workspace::class, ['conversation' => $dm->id])->assertSet('activeConversationId', $dm->id);
    Livewire::actingAs($this->outsider)->test(Workspace::class, ['conversation' => $dm->id])->assertForbidden();
    Livewire::actingAs($this->alice)->test(Workspace::class, ['conversation' => '00000000-0000-0000-0000-000000000000'])->assertNotFound();
});

it('starts a DM from the directory search in the workspace', function () {
    Livewire::actingAs($this->alice)
        ->test(Workspace::class)
        ->set('search', 'Vance')
        ->assertSee('Bob Vance')
        ->call('openPerson', $this->bob->id)
        ->assertSet('search', '')
        ->assertDispatched('chat-url');

    $dm = ChatConversation::where('type', 'direct')->firstOrFail();
    expect($dm->members()->pluck('user_id')->sort()->values()->all())->toBe(collect([$this->alice->id, $this->bob->id])->sort()->values()->all());
});

it('creates a group from the workspace', function () {
    Livewire::actingAs($this->alice)
        ->test(Workspace::class)
        ->call('openNewGroup')
        ->set('groupName', 'Launch team')
        ->call('createGroup')
        ->assertHasErrors('groupMembers')
        ->call('addGroupMember', $this->bob->id)
        ->call('createGroup')
        ->assertHasNoErrors()
        ->assertSet('showNewGroup', false);

    expect(ChatConversation::where('name', 'Launch team')->first()->members()->count())->toBe(2);
});

it('refuses to open a conversation the user is not in', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);

    Livewire::actingAs($this->outsider)->test(Workspace::class, ['conversation' => $dm->id])->assertForbidden();
    Livewire::actingAs($this->outsider)->test(ConversationPane::class, ['conversationId' => $dm->id])->assertForbidden();
});

it('sends, renders, edits and reacts in a conversation pane', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);

    $pane = Livewire::actingAs($this->alice)
        ->test(ConversationPane::class, ['conversationId' => $dm->id])
        ->assertSee('Say hello');

    expect($pane->call('send', 'Hi **Bob**')->effects['returns'][0])->toBe(['ok' => true]);
    $pane->assertSee('<strong>Bob</strong>', false);

    $message = ChatMessage::firstOrFail();
    $pane->call('startEdit', $message->id)
        ->assertSet('editingId', $message->id)
        ->set('editBody', 'Hi Bob, updated')
        ->call('saveEdit')
        ->assertSet('editingId', null)
        ->assertSeeHtml('title="Edited')
        ->call('react', $message->id, '👍')
        ->assertSee('👍');

    // The other side sees it as unread until the pane is opened
    Livewire::actingAs($this->bob)->test(Launcher::class)->assertSet('totalUnread', 1);
    Livewire::actingAs($this->bob)->test(ConversationPane::class, ['conversationId' => $dm->id])->assertSee('Hi Bob, updated');
    Livewire::actingAs($this->bob)->test(Launcher::class)->assertSet('totalUnread', 0);
});

it('returns an error instead of failing when sending is refused', function () {
    $group = $this->chat->createGroup($this->alice, 'Archived', null, [$this->bob->id]);
    $pane = Livewire::actingAs($this->bob)->test(ConversationPane::class, ['conversationId' => $group->id]);
    $this->chat->setArchived($group, $this->alice, true);

    expect($pane->call('send', 'hello?')->effects['returns'][0])->toHaveKey('error');
});

it('uploads attachments encrypted and serves them only to members', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);

    Livewire::actingAs($this->alice)
        ->test(ConversationPane::class, ['conversationId' => $dm->id])
        ->set('newUploads', [UploadedFile::fake()->image('screen.png', 40, 30)])
        ->assertCount('uploads', 1)
        ->call('send', '');

    $attachment = ChatAttachment::firstOrFail();
    expect($attachment->width)->toBe(40)
        ->and(Storage::disk('local')->get($attachment->path))->not->toStartWith("\x89PNG");

    $this->actingAs($this->bob)->get(route('chat.attachment', $attachment))->assertOk()->assertHeader('Content-Type', 'image/png');
    $this->actingAs($this->outsider)->get(route('chat.attachment', $attachment))->assertForbidden();
});

it('serves ticket hover cards only to people who can see the ticket', function () {
    $category = $this->makeCategory();
    $creator = $this->makeStaff($this->dept, ['create-tickets']);
    $ticket = app(TicketService::class)->create([
        'subject' => 'VPN broken', 'description' => '<p>x</p>', 'category_id' => $category->id, 'priority' => 'high',
        'department_id' => $this->dept->id, 'assignee_employee_id' => $this->employeeOf($this->bob)->id,
        'due_date' => now()->addDay()->toDateString(), 'visibility' => 'participants',
    ], $creator);

    $this->actingAs($this->bob)->getJson(route('chat.ticket.card', $ticket->ticket_number))
        ->assertOk()
        ->assertJson(['number' => $ticket->ticket_number, 'status' => 'Assigned', 'priority' => 'High', 'assignee' => 'Bob Vance']);

    $this->actingAs($this->outsider)->getJson(route('chat.ticket.card', $ticket->ticket_number))->assertForbidden();
    $this->actingAs($this->bob)->getJson(route('chat.ticket.card', 'HRP-2026-99999'))->assertNotFound();
});

it('opens the dock on the remembered conversation and lists rooms otherwise', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);
    $this->chat->send($dm, $this->bob, 'Lunch?');

    Livewire::actingAs($this->alice)
        ->test(Launcher::class)
        ->assertSet('totalUnread', 1)
        ->call('loadDock')
        ->assertSee('Lunch?')
        ->call('loadDock', $dm->id)
        ->assertSet('activeConversationId', $dm->id)
        ->call('closeConversation')
        ->assertSet('activeConversationId', null);

    // A conversation you don't belong to is never restored
    Livewire::actingAs($this->outsider)->test(Launcher::class)->call('loadDock', $dm->id)->assertSet('activeConversationId', null);
});

it('shows analytics to authorised users only', function () {
    $analyst = $this->makeStaff($this->dept, ['view-chat-analytics']);
    app(\App\Services\Chat\DepartmentChannelSync::class)->syncAll();
    $channel = ChatConversation::where('department_id', $this->dept->id)->first();
    $this->chat->send($channel, $this->alice, 'Morning all, HRP-2026-00001 is done');

    Livewire::actingAs($analyst)->test(ChatAnalytics::class)
        ->assertSee('Department activity index')
        ->assertSee('Ticket interconnects');

    Livewire::actingAs($this->alice)->test(ChatAnalytics::class)->assertForbidden();
});

<?php

use App\Events\Chat\ChatActivity;
use App\Events\Chat\MessageChanged;
use App\Events\Chat\MessageSent;
use App\Models\ChatMessage;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatActionException;
use App\Services\Chat\ChatInbox;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Event;
use Tests\Support\ChatTestCase;

uses(ChatTestCase::class);

beforeEach(function () {
    $this->chat = app(ChatService::class);
    $this->dept = $this->makeDepartment('ICT');
    $this->alice = $this->makeStaff($this->dept);
    $this->bob = $this->makeStaff($this->dept);
    $this->carol = $this->makeStaff($this->dept);
    $this->outsider = $this->makeStaff($this->makeDepartment('Finance'));
});

it('opens one direct conversation per pair, from either side', function () {
    $a = $this->chat->openDirect($this->alice, $this->bob);
    $b = $this->chat->openDirect($this->bob, $this->alice);

    expect($a->id)->toBe($b->id)
        ->and($a->members()->count())->toBe(2)
        ->and($a->displayName($this->alice->id))->toBe($this->bob->full_name);
});

it('refuses a conversation with yourself', function () {
    $this->chat->openDirect($this->alice, $this->alice);
})->throws(ChatActionException::class);

it('sends a message, broadcasts it and nudges members', function () {
    Event::fake([MessageSent::class, ChatActivity::class]);
    $room = $this->chat->openDirect($this->alice, $this->bob);

    $message = $this->chat->send($room, $this->alice, 'Hello **Bob**, see HRP-2026-00001');

    expect($message->ticket_ref_count)->toBe(1)
        ->and($room->fresh()->last_message_at)->not->toBeNull()
        ->and(app(ChatInbox::class)->totalUnread($this->bob))->toBe(1)
        ->and(app(ChatInbox::class)->totalUnread($this->alice))->toBe(0);

    Event::assertDispatched(MessageSent::class, fn ($e) => $e->message->is($message));
    Event::assertDispatched(ChatActivity::class, fn ($e) => $e->userIds === [$this->bob->id] && ! $e->silent && $e->preview !== '');

    // Bodies are encrypted at rest
    expect(DB::table('chat_messages')->where('id', $message->id)->value('body'))->not->toContain('Hello');
});

it('notifies muted members silently', function () {
    Event::fake([ChatActivity::class, MessageSent::class]);
    $room = $this->chat->openDirect($this->alice, $this->bob);
    $this->chat->setPreference($room, $this->bob, 'muted', true);

    $this->chat->send($room, $this->alice, 'ping');

    Event::assertDispatched(ChatActivity::class, fn ($e) => $e->userIds === [$this->bob->id] && $e->silent);
    expect(app(ChatInbox::class)->totalUnread($this->bob))->toBe(0);
});

it('blocks non-members and archived rooms', function () {
    $room = $this->chat->openDirect($this->alice, $this->bob);

    expect(fn () => $this->chat->send($room, $this->outsider, 'hi'))->toThrow(ChatActionException::class)
        ->and(ChatAccess::isMember($this->outsider, $room))->toBeFalse();

    $group = $this->chat->createGroup($this->alice, 'Launch', null, [$this->bob->id]);
    $this->chat->setArchived($group, $this->alice, true);

    expect(fn () => $this->chat->send($group, $this->bob, 'hi'))->toThrow(ChatActionException::class, 'archived');
});

it('lets only the sender edit or delete, within 15 minutes', function () {
    Event::fake([MessageSent::class, MessageChanged::class, ChatActivity::class]);
    $room = $this->chat->openDirect($this->alice, $this->bob);
    $message = $this->chat->send($room, $this->alice, 'Draft');

    expect(fn () => $this->chat->edit($message, $this->bob, 'hijack'))->toThrow(ChatActionException::class);

    $this->chat->edit($message, $this->alice, 'Final');
    expect($message->fresh()->body)->toBe('Final')->and($message->fresh()->edited_at)->not->toBeNull();
    Event::assertDispatched(MessageChanged::class, fn ($e) => $e->change === 'edited');

    $this->travel(16)->minutes();
    expect(fn () => $this->chat->edit($message->fresh(), $this->alice, 'Too late'))->toThrow(ChatActionException::class, 'within 15 minutes')
        ->and(fn () => $this->chat->delete($message->fresh(), $this->alice))->toThrow(ChatActionException::class, 'deleted within');
});

it('soft deletes so history keeps a placeholder', function () {
    $room = $this->chat->openDirect($this->alice, $this->bob);
    $message = $this->chat->send($room, $this->alice, 'oops');

    $this->chat->delete($message, $this->alice);

    expect(ChatMessage::find($message->id))->toBeNull()
        ->and(ChatMessage::withTrashed()->find($message->id)->trashed())->toBeTrue();
});

it('manages groups: cap, admins, removal and ownership hand-over', function () {
    config(['chat.group_member_cap' => 3]);

    expect(fn () => $this->chat->createGroup($this->alice, 'Too big', null, [$this->bob->id, $this->carol->id, $this->outsider->id]))
        ->toThrow(ChatActionException::class, 'limited to 3');

    $group = $this->chat->createGroup($this->alice, 'Payroll taskforce', 'Migration sprint', [$this->bob->id]);
    expect(ChatAccess::membership($this->alice, $group)->role)->toBe('owner');

    // Plain members cannot add people
    expect(fn () => $this->chat->addMembers($group, $this->bob, [$this->carol->id]))->toThrow(ChatActionException::class);

    $this->chat->addMembers($group, $this->alice, [$this->carol->id]);
    expect(fn () => $this->chat->addMembers($group, $this->alice, [$this->outsider->id]))->toThrow(ChatActionException::class, 'limited to 3');

    $this->chat->setRole($group, $this->alice, $this->carol->id, 'admin');
    $this->chat->removeMember($group, $this->carol, $this->bob->id);
    expect(ChatAccess::isMember($this->bob, $group))->toBeFalse();

    $this->chat->leave($group, $this->alice);
    expect(ChatAccess::membership($this->carol, $group)->role)->toBe('owner');

    // System lines record what happened
    expect(ChatMessage::where('conversation_id', $group->id)->where('type', 'system')->count())->toBeGreaterThanOrEqual(4);
});

it('toggles reactions and limits pinning to moderators', function () {
    $group = $this->chat->createGroup($this->alice, 'Team', null, [$this->bob->id]);
    $message = $this->chat->send($group, $this->bob, 'Shipped!');

    $this->chat->toggleReaction($message, $this->alice, '🎉');
    expect($message->reactions()->count())->toBe(1);
    $this->chat->toggleReaction($message, $this->alice, '🎉');
    expect($message->reactions()->count())->toBe(0);

    expect(fn () => $this->chat->togglePin($message, $this->bob))->toThrow(ChatActionException::class);
    $this->chat->togglePin($message, $this->alice);
    expect($message->fresh()->pinned_at)->not->toBeNull();
});

it('marks a room read', function () {
    $room = $this->chat->openDirect($this->alice, $this->bob);
    $this->chat->send($room, $this->alice, 'one');
    $this->chat->send($room, $this->alice, 'two');

    $this->travel(1)->seconds();
    $this->chat->markRead($room->fresh(), $this->bob);

    expect(app(ChatInbox::class)->totalUnread($this->bob))->toBe(0);
});

it('lists inbox rooms with unread counts and the other person for DMs', function () {
    $dm = $this->chat->openDirect($this->alice, $this->bob);
    $this->chat->send($dm, $this->bob, 'hi');
    $this->chat->createGroup($this->alice, 'Group', null, [$this->carol->id]);

    $inbox = app(ChatInbox::class)->conversations($this->alice);

    // DM + group + the ICT department channel created automatically for staff
    expect($inbox)->toHaveCount(3)
        ->and($inbox->pluck('type')->sort()->values()->all())->toBe(['department', 'direct', 'group'])
        ->and($inbox->firstWhere('id', $dm->id)->unread_count)->toBe(1)
        ->and($inbox->firstWhere('id', $dm->id)->displayName($this->alice->id))->toBe($this->bob->full_name);
});

it('survives a broadcasting outage', function () {
    // An unreachable Reverb server must not lose the message
    config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.options.host' => '127.0.0.1', 'broadcasting.connections.reverb.options.port' => 1]);
    app('Illuminate\Broadcasting\BroadcastManager')->purge();

    $room = $this->chat->openDirect($this->alice, $this->bob);
    $message = $this->chat->send($room, $this->alice, 'still saved');

    expect(ChatMessage::find($message->id))->not->toBeNull();
});

<?php

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatService;
use App\Services\Chat\DepartmentChannelSync;
use App\Services\Chat\EmployeeDirectory;
use App\Services\Chat\MessageRenderer;
use Illuminate\Support\Str;
use Tests\Support\ChatTestCase;

uses(ChatTestCase::class);

beforeEach(function () {
    $this->ict = $this->makeDepartment('ICT');
    $this->finance = $this->makeDepartment('Finance');
});

it('searches the directory by name, title, department and number after 3 characters', function () {
    $title = Jobtitle::forceCreate(['id' => (string) Str::uuid(), 'name' => 'Systems Analyst']);
    $viewer = $this->makeStaff($this->ict);
    $jane = $this->makeStaff($this->finance, [], ['first_name' => 'Janeth', 'last_name' => 'Mrisho', 'employee_no' => 'EMP-4411', 'title_id' => $title->id]);
    $this->makeStaff($this->finance, [], ['first_name' => 'Janeth', 'last_name' => 'Gone', 'status' => 'Terminated']);

    $search = app(EmployeeDirectory::class);
    $ids = fn ($term) => $search->search($term, $viewer)['people']->pluck('user_id')->all();

    expect($ids('Ja'))->toBe([])
        ->and($ids('janeth'))->toBe([$jane->id])          // inactive staff are excluded
        ->and($ids('mrisho'))->toBe([$jane->id])
        ->and($ids('systems anal'))->toBe([$jane->id])
        ->and($ids('4411'))->toBe([$jane->id])
        ->and($ids('Janeth Finance'))->toBe([$jane->id])
        ->and($search->search('Janeth', $viewer)['people']->first())->toMatchArray(['job_title' => 'Systems Analyst', 'department' => $this->finance->name]);

    // You never find yourself
    expect($ids(Employee::where('user_id', $viewer->id)->value('first_name')))->not->toContain($viewer->id);
});

it('creates department channels that follow hires, transfers and terminations', function () {
    $sync = app(DepartmentChannelSync::class);
    $alice = $this->makeStaff($this->ict);
    $bob = $this->makeStaff($this->ict);

    $sync->syncAll();
    $ictChannel = ChatConversation::where('department_id', $this->ict->id)->firstOrFail();
    expect($ictChannel->members()->count())->toBe(2)->and($ictChannel->name)->toBe($this->ict->name);

    // Hire: observer queues a sync (sync queue in tests)
    $carol = $this->makeStaff($this->ict);
    expect(ChatAccess::isMember($carol, $ictChannel))->toBeTrue();

    // Transfer
    $this->employeeOf($bob)->update(['department_id' => $this->finance->id]);
    $financeChannel = ChatConversation::where('department_id', $this->finance->id)->firstOrFail();
    expect(ChatAccess::isMember($bob, $ictChannel))->toBeFalse()
        ->and(ChatAccess::isMember($bob, $financeChannel))->toBeTrue();

    // Termination removes access, including to history
    $this->employeeOf($alice)->update(['status' => 'Terminated']);
    expect(ChatAccess::isMember($alice, $ictChannel))->toBeFalse();
});

it('makes department supervisors channel admins who can moderate', function () {
    $head = Jobtitle::forceCreate(['id' => (string) Str::uuid(), 'name' => 'Head of ICT']);
    $this->ict->update(['supervisor_title_id' => $head->id]);
    $supervisor = $this->makeStaff($this->finance, [], ['title_id' => $head->id]);
    $member = $this->makeStaff($this->ict);

    app(DepartmentChannelSync::class)->syncAll();
    $channel = ChatConversation::where('department_id', $this->ict->id)->firstOrFail();

    expect(ChatAccess::membership($supervisor, $channel)->role)->toBe('admin')
        ->and(ChatAccess::canModerate($supervisor, $channel))->toBeTrue()
        ->and(ChatAccess::canModerate($member, $channel))->toBeFalse()
        ->and(ChatAccess::canManageMembers($supervisor, $channel))->toBeFalse(); // roster follows HR
});

it('renders safe markdown with ticket chips', function () {
    $html = app(MessageRenderer::class)->toHtml('**Done** <script>alert(1)</script> [x](javascript:alert(1)) see HRP-2026-00042 and `HRP-2026-00001` https://example.com');

    expect($html)->toContain('<strong>Done</strong>')
        ->not->toContain('<script>')
        ->not->toContain('javascript:')
        ->toContain('class="chat-ticket-ref" data-ticket="HRP-2026-00042"')
        ->toContain('<code>HRP-2026-00001</code>')                 // not linked inside code
        ->toContain('href="https://example.com" target="_blank" rel="noopener noreferrer"');

    expect(MessageRenderer::isEmojiOnly('🎉🎉'))->toBeTruthy()
        ->and(MessageRenderer::isEmojiOnly('ok 🎉'))->toBeFalsy();
});

it('prunes messages older than the retention period', function () {
    $alice = $this->makeStaff($this->ict);
    $bob = $this->makeStaff($this->ict);
    $room = app(ChatService::class)->openDirect($alice, $bob);
    $old = app(ChatService::class)->send($room, $alice, 'ancient');
    $old->forceFill(['created_at' => now()->subYears(8)])->saveQuietly();
    $recent = app(ChatService::class)->send($room, $alice, 'recent');

    $this->artisan('chat:prune')->assertSuccessful();

    expect(ChatMessage::withTrashed()->find($old->id))->toBeNull()
        ->and(ChatMessage::find($recent->id))->not->toBeNull();
});

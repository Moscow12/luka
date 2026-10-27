<?php

namespace App\Livewire\Chat;

use App\Livewire\Chat\Concerns\SearchesDirectory;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatActionException;
use App\Services\Chat\ChatInbox;
use App\Services\Chat\ChatService;
use App\Services\Chat\EmployeeDirectory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Full three-pane chat workspace: rooms, the open conversation, and its details.
 */
class Workspace extends Component
{
    use SearchesDirectory;

    #[Locked]
    public string $userId;

    public ?string $activeConversationId = null;

    // New group
    public bool $showNewGroup = false;

    public string $groupName = '';

    public string $groupTopic = '';

    /** @var array<string, string> user id => name */
    public array $groupMembers = [];

    public string $groupSearch = '';

    public function mount(?ChatConversation $conversation = null)
    {
        $this->userId = Auth::id();

        if ($conversation?->exists) {
            abort_unless(ChatAccess::isMember(Auth::user(), $conversation), 403);
            $this->activeConversationId = $conversation->id;
        }

        if (request()->query('new') === 'group') {
            $this->openNewGroup();
        }
    }

    public function openConversation(string $id): void
    {
        if (! ChatAccess::isMember(Auth::user(), $id)) {
            $this->notice = 'That conversation is no longer available.';

            return;
        }

        $this->activeConversationId = $id;
        $this->dispatch('chat-url', url: route('chat.index', $id));
    }

    public function closeConversation(): void
    {
        $this->activeConversationId = null;
        $this->dispatch('chat-url', url: route('chat.index'));
    }

    #[On('echo-private:chat.user.{userId},.chat.activity')]
    #[On('chat-read')]
    public function refreshList($payload = null): void
    {
        // Re-render updates unread counts and ordering
    }

    #[On('chat-left')]
    public function onLeft(string $id): void
    {
        if ($this->activeConversationId === $id) {
            $this->closeConversation();
        }
    }

    // ---------------------------------------------------------------- new group

    public function openNewGroup(): void
    {
        $this->resetErrorBag();
        $this->reset(['groupName', 'groupTopic', 'groupMembers', 'groupSearch']);
        $this->showNewGroup = true;
    }

    public function closeNewGroup(): void
    {
        $this->showNewGroup = false;
    }

    public function addGroupMember(string $userId): void
    {
        $user = User::find($userId);
        if ($user && $user->id !== Auth::id()) {
            $this->groupMembers[$user->id] = $user->full_name;
        }
        $this->groupSearch = '';
    }

    public function removeGroupMember(string $userId): void
    {
        unset($this->groupMembers[$userId]);
    }

    public function createGroup(ChatService $chat): void
    {
        $this->validate([
            'groupName' => ['required', 'string', 'max:120'],
            'groupTopic' => ['nullable', 'string', 'max:500'],
            'groupMembers' => ['array', 'min:1', 'max:'.(config('chat.group_member_cap') - 1)],
        ], ['groupMembers.min' => 'Add at least one person to the group.'], ['groupName' => 'group name']);

        try {
            $conversation = $chat->createGroup(Auth::user(), $this->groupName, $this->groupTopic, array_keys($this->groupMembers));
        } catch (ChatActionException $e) {
            $this->addError('groupName', $e->getMessage());

            return;
        }

        $this->showNewGroup = false;
        $this->openConversation($conversation->id);
    }

    public function render()
    {
        $candidates = $this->showNewGroup && mb_strlen(trim($this->groupSearch)) >= config('chat.search_min_chars')
            ? app(EmployeeDirectory::class)->search($this->groupSearch, Auth::user())['people']
                ->reject(fn ($p) => isset($this->groupMembers[$p['user_id']]))
            : collect();

        return view('livewire.chat.workspace', [
            'conversations' => app(ChatInbox::class)->conversations(Auth::user()),
            'groupCandidates' => $candidates,
        ]);
    }
}

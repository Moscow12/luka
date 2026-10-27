<?php

namespace App\Livewire\Chat;

use App\Livewire\Chat\Concerns\SearchesDirectory;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatInbox;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The floating chat bubble + quick dock present on every page.
 * The dock content loads lazily the first time it is opened.
 */
class Launcher extends Component
{
    use SearchesDirectory;

    #[Locked]
    public string $userId;

    public bool $hideBubble = false;

    public bool $loaded = false;

    public int $totalUnread = 0;

    public ?string $activeConversationId = null;

    public function mount(bool $hideBubble = false)
    {
        $this->userId = Auth::id();
        $this->hideBubble = $hideBubble;
        $this->totalUnread = app(ChatInbox::class)->totalUnread(Auth::user());
    }

    public function loadDock(?string $remembered = null): void
    {
        $this->loaded = true;

        if ($remembered && ! $this->activeConversationId && ChatAccess::isMember(Auth::user(), $remembered)) {
            $this->activeConversationId = $remembered;
        }
    }

    public function openConversation(string $id): void
    {
        $this->loaded = true;

        if (! ChatAccess::isMember(Auth::user(), $id)) {
            $this->notice = 'That conversation is no longer available.';

            return;
        }

        $this->activeConversationId = $id;
        $this->dispatch('chat-dock-active', id: $id);
    }

    public function closeConversation(): void
    {
        $this->activeConversationId = null;
        $this->dispatch('chat-dock-active', id: null);
        $this->refreshUnread();
    }

    #[On('echo-private:chat.user.{userId},.chat.activity')]
    #[On('chat-read')]
    public function refreshUnread($payload = null): void
    {
        $this->totalUnread = app(ChatInbox::class)->totalUnread(Auth::user());
    }

    #[On('chat-left')]
    public function onLeft(string $id): void
    {
        if ($this->activeConversationId === $id) {
            $this->closeConversation();
        }
    }

    public function render()
    {
        return view('livewire.chat.launcher', [
            'conversations' => $this->loaded && ! $this->activeConversationId
                ? app(ChatInbox::class)->conversations(Auth::user())
                : collect(),
        ]);
    }
}

<?php

namespace App\Livewire\Chat\Concerns;

use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatActionException;
use App\Services\Chat\ChatService;
use App\Services\Chat\EmployeeDirectory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Global employee search for the dock and workspace (PRD 3).
 * The host component provides openConversation(string $id).
 */
trait SearchesDirectory
{
    public string $search = '';

    public ?string $notice = null;

    #[Computed]
    public function searchResults(): array
    {
        return app(EmployeeDirectory::class)->search($this->search, Auth::user());
    }

    public function openPerson(string $userId): void
    {
        try {
            $conversation = app(ChatService::class)->openDirect(Auth::user(), User::findOrFail($userId));
        } catch (ChatActionException $e) {
            $this->notice = $e->getMessage();

            return;
        }

        $this->search = '';
        $this->openConversation($conversation->id);
    }

    public function openDepartmentChannel(string $departmentId): void
    {
        $channel = ChatConversation::where('department_id', $departmentId)->first();

        if (! $channel || ! ChatAccess::isMember(Auth::user(), $channel)) {
            $this->notice = 'You are not a member of that department channel.';

            return;
        }

        $this->search = '';
        $this->openConversation($channel->id);
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->notice = null;
    }
}

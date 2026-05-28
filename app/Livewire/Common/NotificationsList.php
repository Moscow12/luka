<?php

namespace App\Livewire\Common;

use App\Livewire\Common\Concerns\BuildsNotifications;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsList extends Component
{
    use BuildsNotifications;

    public $pendingApprovals = [];

    public function mount()
    {
        $this->loadPendingApprovals();
    }

    #[On('refreshNotifications')]
    public function loadPendingApprovals()
    {
        $this->pendingApprovals = $this->buildNotifications(10);
    }

    public function dismiss(string $key, ?string $url = null)
    {
        $this->dismissNotification($key);
        $this->loadPendingApprovals();
        $this->dispatch('refreshNotifications');

        if ($url) {
            return redirect($url);
        }
    }

    public function render()
    {
        return view('livewire.common.notifications-list');
    }
}

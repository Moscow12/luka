<?php

namespace App\Livewire\Common;

use App\Livewire\Common\Concerns\BuildsNotifications;
use Livewire\Attributes\On;
use Livewire\Component;

class Notifications extends Component
{
    use BuildsNotifications;

    public $pendingApprovals = [];
    public $notificationCount = 0;

    public function mount()
    {
        $this->loadPendingApprovals();
    }

    #[On('refreshNotifications')]
    public function loadPendingApprovals()
    {
        $this->pendingApprovals = $this->buildNotifications(5);
        $this->notificationCount = count($this->pendingApprovals);
    }

    public function render()
    {
        return view('livewire.common.notifications');
    }
}

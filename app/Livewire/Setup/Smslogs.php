<?php

namespace App\Livewire\Setup;

use App\Models\SmsLog;
use Livewire\Component;
use Livewire\WithPagination;

class Smslogs extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = 'all';

    public $dateFrom = '';

    public $dateTo = '';

    public $showModal = false;

    public $selectedLog = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($logId)
    {
        $this->selectedLog = SmsLog::with(['smsApiSetting', 'user'])->findOrFail($logId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        $logs = SmsLog::query()
            ->with(['smsApiSetting', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('phone_number', 'like', '%'.$this->search.'%')
                        ->orWhere('message', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => SmsLog::count(),
            'sent' => SmsLog::whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
            'pending' => SmsLog::where('status', 'pending')->count(),
        ];

        return view('livewire.setup.smslogs', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }
}

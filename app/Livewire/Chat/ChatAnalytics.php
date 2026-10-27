<?php

namespace App\Livewire\Chat;

use App\Models\departments;
use App\Services\Chat\ChatMetricsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatAnalytics extends Component
{
    public $dateFrom = '';

    public $dateTo = '';

    public $departmentId = '';

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('view-chat-analytics'), 403);

        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated()
    {
        [$from, $to] = $this->range();
        $this->dispatch('chat-analytics-updated', data: app(ChatMetricsService::class)->dailyMessages($from, $to));
    }

    protected function range(): array
    {
        $from = Carbon::parse($this->dateFrom ?: now()->subDays(29))->startOfDay();
        $to = Carbon::parse($this->dateTo ?: now())->endOfDay();

        // Keep the range sane (max ~1 year) and ordered
        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366)->startOfDay();
        }

        return [$from, $to];
    }

    public function render(ChatMetricsService $metrics)
    {
        [$from, $to] = $this->range();

        return view('livewire.chat.chat-analytics', [
            'summary' => $metrics->summary($from, $to),
            'activity' => $metrics->departmentActivity($from, $to, $this->departmentId ?: null),
            'media' => $metrics->mediaByType($from, $to),
            'trend' => $metrics->dailyMessages($from, $to),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
        ]);
    }
}

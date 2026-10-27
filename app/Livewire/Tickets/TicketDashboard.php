<?php

namespace App\Livewire\Tickets;

use App\Models\departments;
use App\Services\Tickets\TicketMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TicketDashboard extends Component
{
    public $dateFrom = '';

    public $dateTo = '';

    public $departmentId = '';

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('view-ticket-reports'), 403);

        $this->dateFrom = now()->subMonths(3)->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated()
    {
        // Charts are re-drawn from the new data on the client
        $this->dispatch('ticket-charts-updated', data: $this->chartData(app(TicketMetricsService::class)));
    }

    protected function filters(): array
    {
        return [
            'from' => $this->dateFrom ?: null,
            'to' => $this->dateTo ?: null,
            'department_id' => $this->departmentId ?: null,
        ];
    }

    protected function chartData(TicketMetricsService $metrics): array
    {
        $workload = $metrics->workload($this->filters());

        return [
            'trend' => $metrics->artTrend(['department_id' => $this->departmentId ?: null]),
            'departments' => [
                'labels' => $workload['departments']->pluck('name')->all(),
                'values' => $workload['departments']->pluck('active')->all(),
            ],
        ];
    }

    public function render(TicketMetricsService $metrics)
    {
        $filters = $this->filters();
        $workload = $metrics->workload($filters);

        return view('livewire.tickets.ticket-dashboard', [
            'summary' => $metrics->summary($filters),
            'workload' => $workload,
            'scorecards' => $metrics->scorecards($filters),
            'chartData' => $this->chartData($metrics),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
        ]);
    }
}

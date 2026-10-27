<div>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1">Ticket Analytics</h4>
            <p class="text-muted mb-0">Workload, velocity and quality of ticket handling</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small">Created from</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small">Created to</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All departments</option>
                        @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI tiles --}}
    @php
        $tiles = [
            ['Total workload', number_format($summary['total']), 'fa-ticket', 'All tickets logged in range'],
            ['Active pipeline', number_format($summary['active']), 'fa-gears', 'Open, assigned and in progress'],
            ['Overdue (SLA breach)', number_format($summary['overdue']), 'fa-triangle-exclamation', 'Past due and not resolved'],
            ['Avg. resolution time', $summary['art_hours'] === null ? '—' : ($summary['art_hours'] >= 48 ? round($summary['art_hours'] / 24, 1).' d' : $summary['art_hours'].' h'), 'fa-stopwatch', 'Created → resolved'],
            ['Resolved / closed', number_format($summary['resolved']), 'fa-circle-check', 'Work completed'],
            ['Reopen defect rate', $summary['reopen_rate'] === null ? '—' : $summary['reopen_rate'].'%', 'fa-rotate-left', 'Reopened ÷ resolved'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($tiles as [$label, $value, $icon, $hint])
            <div class="col-6 col-lg-2">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1"><i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}</div>
                        <div class="h3 mb-0 {{ $icon === 'fa-triangle-exclamation' && $summary['overdue'] > 0 ? 'text-danger' : '' }}">{{ $value }}</div>
                        <div class="text-muted small">{{ $hint }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Average resolution time per month (hours)</h6></div>
                <div class="card-body"><div id="ticket-art-chart" wire:ignore style="min-height: 280px;"></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Active tickets by department</h6></div>
                <div class="card-body"><div id="ticket-dept-chart" wire:ignore style="min-height: 280px;"></div></div>
            </div>
        </div>
    </div>

    {{-- Workload heat table --}}
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-table-cells me-2"></i>Workload distribution by officer</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 text-center">
                    <thead class="table-light"><tr><th class="text-start">Officer</th><th>Total</th><th>Active</th><th>Overdue</th><th>Resolved/closed</th></tr></thead>
                    <tbody>
                        @php
                            $max = [
                                'total' => max(1, $workload['assignees']->max('total')),
                                'active' => max(1, $workload['assignees']->max('active')),
                                'overdue' => max(1, $workload['assignees']->max('overdue')),
                                'resolved' => max(1, $workload['assignees']->max('resolved')),
                            ];
                        @endphp
                        @forelse($workload['assignees'] as $row)
                            <tr>
                                <td class="text-start">{{ $row['name'] }}</td>
                                @foreach(['total', 'active', 'overdue', 'resolved'] as $col)
                                    @php $alpha = $row[$col] ? 0.12 + 0.6 * ($row[$col] / $max[$col]) : 0; @endphp
                                    <td style="background-color: rgba({{ $col === 'overdue' ? '220, 53, 69' : '42, 120, 214' }}, {{ round($alpha, 2) }});">{{ $row[$col] }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted py-4">No tickets in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Scorecards --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa-solid fa-ranking-star me-2"></i>Staff ticket scorecards</h6>
            <small class="text-muted">Score = 40% on-time closure + 30% quality rating + 30% first-time-right (1 − reopen rate)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Officer</th><th>Department</th><th class="text-center">Assigned</th><th class="text-center">Closed</th><th class="text-center">On time</th><th class="text-center">Avg rating</th><th class="text-center">Reopen rate</th><th class="text-center">ART</th><th style="width: 160px;">Score</th></tr>
                    </thead>
                    <tbody>
                        @forelse($scorecards as $card)
                            <tr>
                                <td>{{ $card['name'] }}</td>
                                <td class="small text-muted">{{ $card['department'] }}</td>
                                <td class="text-center">{{ $card['assigned'] }}</td>
                                <td class="text-center">{{ $card['closed'] }}</td>
                                <td class="text-center">{{ $card['on_time_rate'] === null ? '—' : $card['on_time_rate'].'%' }}</td>
                                <td class="text-center">{{ $card['avg_rating'] === null ? '—' : $card['avg_rating'].' / 5' }}</td>
                                <td class="text-center">{{ $card['reopen_rate'] === null ? '—' : $card['reopen_rate'].'%' }}</td>
                                <td class="text-center">{{ $card['art_hours'] === null ? '—' : $card['art_hours'].' h' }}</td>
                                <td>@include('livewire.tickets.partials.score-bar', ['score' => $card['score']])</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No assigned tickets in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
(function () {
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const palette = () => isDark()
        ? { series: '#3987e5', text: '#c3c2b7', grid: 'rgba(255,255,255,0.08)' }
        : { series: '#2a78d6', text: '#52514e', grid: 'rgba(0,0,0,0.06)' };
    const charts = {};

    function render(data) {
        if (typeof ApexCharts === 'undefined') { return; }
        const p = palette();
        const base = {
            chart: { height: 280, toolbar: { show: false }, fontFamily: 'inherit', foreColor: p.text, background: 'transparent' },
            colors: [p.series],
            grid: { borderColor: p.grid, strokeDashArray: 3 },
            dataLabels: { enabled: false },
            theme: { mode: isDark() ? 'dark' : 'light' },
        };

        const defs = {
            'ticket-art-chart': Object.assign({}, base, {
                chart: Object.assign({}, base.chart, { type: 'line' }),
                series: [{ name: 'Avg resolution (h)', data: data.trend.values }],
                xaxis: { categories: data.trend.labels },
                stroke: { width: 2, curve: 'straight' },
                markers: { size: 4, strokeWidth: 2 },
                tooltip: { y: { formatter: v => v === null ? 'No resolutions' : v + ' h' } },
                noData: { text: 'No resolved tickets yet' },
            }),
            'ticket-dept-chart': Object.assign({}, base, {
                chart: Object.assign({}, base.chart, { type: 'bar' }),
                series: [{ name: 'Active tickets', data: data.departments.values }],
                xaxis: { categories: data.departments.labels },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, borderRadiusApplication: 'end', barHeight: '60%' } },
                tooltip: { y: { formatter: v => v + ' active' } },
                noData: { text: 'No tickets in this range' },
            }),
        };

        Object.entries(defs).forEach(([id, options]) => {
            const el = document.getElementById(id);
            if (!el) { return; }
            if (charts[id]) { charts[id].destroy(); }
            el.innerHTML = '';
            charts[id] = new ApexCharts(el, options);
            charts[id].render();
        });
    }

    let latest = @js($chartData);
    render(latest);
    window.addEventListener('ticket-charts-updated', e => { latest = e.detail.data; render(latest); });
    new MutationObserver(() => render(latest)).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
})();
</script>
</div>

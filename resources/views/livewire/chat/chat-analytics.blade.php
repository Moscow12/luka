@php
    $bytes = fn ($b) => $b >= 1073741824 ? round($b / 1073741824, 2).' GB' : ($b >= 1048576 ? round($b / 1048576, 1).' MB' : round($b / 1024).' KB');
@endphp
<div>
    <div class="row mb-4">
        <div class="col-12 d-md-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Chat Analytics</h4>
                <p class="text-muted mb-0">Adoption, media volume and links between chat and tickets</p>
            </div>
            <a href="{{ route('chat.index') }}" class="btn btn-outline-primary mt-2 mt-md-0"><i class="fa-regular fa-comments me-1"></i>Open chat</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3"><label class="form-label small">From</label><input type="date" class="form-control" wire:model.live="dateFrom"></div>
                <div class="col-6 col-md-3"><label class="form-label small">To</label><input type="date" class="form-control" wire:model.live="dateTo"></div>
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

    @php
        $tiles = [
            ['Messages sent', number_format($summary['messages']), 'fa-message', 'Text messages in range'],
            ['Active people', number_format($summary['active_users']), 'fa-user-check', 'Sent at least one message'],
            ['Media volume', $bytes($summary['media_bytes']), 'fa-photo-film', $media['files'].' file(s) shared'],
            ['Ticket interconnects', number_format($summary['interconnects']), 'fa-link', 'HRP-YYYY-NNNNN mentions'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($tiles as [$label, $value, $icon, $hint])
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small mb-1"><i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}</div>
                    <div class="h3 mb-0">{{ $value }}</div>
                    <div class="text-muted small">{{ $hint }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Messages per day</h6></div>
                <div class="card-body"><div id="chat-trend-chart" wire:ignore style="min-height: 280px;"></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Storage by type</h6></div>
                <div class="card-body">
                    @php $total = max(1, $media['images'] + $media['documents']); @endphp
                    @foreach(['images' => ['Images', 'fa-image'], 'documents' => ['Documents', 'fa-file-lines']] as $key => [$label, $icon])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1"><span><i class="fa-solid {{ $icon }} me-1 text-muted"></i>{{ $label }}</span><span class="fw-semibold">{{ $bytes($media[$key]) }}</span></div>
                            <div class="progress" style="height: 8px;"><div class="progress-bar" style="width: {{ round($media[$key] / $total * 100) }}%"></div></div>
                        </div>
                    @endforeach
                    <p class="small text-muted mb-0">Messages and files are kept for {{ config('chat.retention_years') }} years, then removed automatically.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Department activity index</h6>
            <small class="text-muted">Average daily share of the department who posted in its channel</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Channel</th><th class="text-center">Members</th><th class="text-center">Active days</th><th class="text-center">Peak daily senders</th><th style="width: 30%;">Activity index</th></tr></thead>
                    <tbody>
                        @forelse($activity as $row)
                            <tr>
                                <td><span class="text-muted">#</span>{{ $row['name'] }}</td>
                                <td class="text-center">{{ $row['members'] }}</td>
                                <td class="text-center">{{ $row['active_days'] }}</td>
                                <td class="text-center">{{ $row['peak_active'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-fill" style="height: 8px;"><div class="progress-bar {{ $row['index'] < 5 ? 'bg-warning' : '' }}" style="width: {{ min(100, $row['index']) }}%"></div></div>
                                        <span class="small fw-semibold" style="min-width: 48px;">{{ $row['index'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No department channels yet. Run <code>php artisan chat:sync-departments</code>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
(function () {
    let chart = null;
    const draw = (data) => {
        const el = document.getElementById('chat-trend-chart');
        if (!el || typeof ApexCharts === 'undefined') return;
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        if (chart) chart.destroy();
        el.innerHTML = '';
        chart = new ApexCharts(el, {
            chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit', foreColor: dark ? '#c3c2b7' : '#52514e', background: 'transparent' },
            series: [{ name: 'Messages', data: data.values }],
            xaxis: { categories: data.labels, tickAmount: 8 },
            colors: [dark ? '#3987e5' : '#2a78d6'],
            stroke: { width: 2, curve: 'straight' },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0 } },
            dataLabels: { enabled: false },
            grid: { borderColor: dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)', strokeDashArray: 3 },
            tooltip: { theme: dark ? 'dark' : 'light' },
        });
        chart.render();
    };
    let latest = @js($trend);
    draw(latest);
    window.addEventListener('chat-analytics-updated', (e) => { latest = e.detail.data; draw(latest); });
    new MutationObserver(() => draw(latest)).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
})();
</script>
</div>

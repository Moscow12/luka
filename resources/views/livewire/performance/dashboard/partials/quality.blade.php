{{-- SMART goal quality and planning gaps. Expects: $smart, $issues --}}
<div class="card mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Plan quality (SMART)</h6>
    </div>
    <div class="card-body">
        @if($smart['total'] === 0)
            <p class="text-muted small mb-0">No goals have been set yet.</p>
        @else
            <div class="d-flex justify-content-between align-items-baseline mb-3">
                <span class="small text-muted">Goals meeting all five criteria</span>
                <span class="fw-semibold">{{ $smart['compliant'] }} of {{ $smart['total'] }}</span>
            </div>
            @foreach($smart['criteria'] as $letter => $criterion)
                @php($share = $criterion['met'] / $smart['total'] * 100)
                <div class="mb-2" title="{{ $criterion['met'] }} of {{ $smart['total'] }} goals are {{ strtolower($criterion['label']) }}">
                    <div class="d-flex justify-content-between small">
                        <span><span class="pd-smart pd-smart-ok me-1">{{ $letter }}</span>{{ $criterion['label'] }}</span>
                        <span class="fw-semibold">{{ number_format($share, 0) }}%</span>
                    </div>
                    <div class="pd-track bg-primary-subtle mt-1"><div class="pd-fill bg-primary" style="width: {{ $share }}%"></div></div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i>Needs attention</h6>
        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ count($issues) }}</span>
    </div>
    <div class="card-body p-0">
        @forelse(array_slice($issues, 0, 12) as $issue)
            @php($icon = ['danger' => 'fa-circle-exclamation', 'warning' => 'fa-triangle-exclamation', 'info' => 'fa-circle-info'][$issue['level']])
            <div class="d-flex gap-2 px-3 py-2 border-bottom small">
                <i class="fa-solid {{ $icon }} text-{{ $issue['level'] }} mt-1"></i>
                <span>{{ $issue['text'] }}</span>
            </div>
        @empty
            <div class="px-3 py-3 small text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i>No planning gaps found.</div>
        @endforelse
        @if(count($issues) > 12)
            <div class="px-3 py-2 small text-muted">and {{ count($issues) - 12 }} more…</div>
        @endif
    </div>
</div>

@if($score === null)
    <span class="text-muted small">No closed tickets</span>
@else
    <div class="d-flex align-items-center gap-2">
        <div class="progress flex-fill" style="height: 8px;" role="progressbar" aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar {{ $score >= 75 ? 'bg-success' : ($score >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $score }}%"></div>
        </div>
        <span class="small fw-semibold">{{ number_format($score, 0) }}</span>
    </div>
@endif

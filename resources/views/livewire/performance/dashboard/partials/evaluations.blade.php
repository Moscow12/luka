{{-- Evaluation pipeline and rating spread. Expects: $evaluations (from PerformanceDashboard::evaluationSummary) --}}
<div class="card mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fa-solid fa-star me-2 text-primary"></i>Evaluation progress</h6>
    </div>
    <div class="card-body">
        @if($evaluations['total'] === 0)
            <p class="text-muted small mb-0">No employee plans yet, so nothing to evaluate.</p>
        @else
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="small text-muted">Approved evaluations</span>
                <span class="fw-semibold">{{ $evaluations['stages']['approved']['count'] }} of {{ $evaluations['total'] }}
                    <span class="text-muted fw-normal">({{ number_format($evaluations['completion'], 0) }}%)</span></span>
            </div>

            <div class="pd-seg mb-3" role="img"
                 aria-label="@foreach($evaluations['stages'] as $stage){{ $stage['label'] }} {{ $stage['count'] }}. @endforeach">
                @foreach($evaluations['stages'] as $stage)
                    @if($stage['count'] > 0)
                        <span class="bg-{{ $stage['color'] }} {{ $stage['label'] === 'Not started' ? 'opacity-25' : '' }}"
                              style="width: {{ $stage['count'] / $evaluations['total'] * 100 }}%"
                              title="{{ $stage['label'] }}: {{ $stage['count'] }}"></span>
                    @endif
                @endforeach
            </div>

            <ul class="list-unstyled small mb-3">
                @foreach($evaluations['stages'] as $stage)
                    <li class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                        <span><span class="pd-dot bg-{{ $stage['color'] }} {{ $stage['label'] === 'Not started' ? 'opacity-25' : '' }}"></span>{{ $stage['label'] }}</span>
                        <span class="fw-semibold">{{ $stage['count'] }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="small text-muted">Average final score</span>
                <span class="fs-5 fw-semibold">{{ $evaluations['average_score'] === null ? '—' : number_format($evaluations['average_score'], 1).'%' }}</span>
            </div>

            @php($maxRating = max(1, max($evaluations['ratings'])))
            <div class="small text-muted mb-1">Rating spread (approved only)</div>
            @foreach($evaluations['ratings'] as $label => $count)
                <div class="d-flex align-items-center gap-2 mb-1" title="{{ $label }}: {{ $count }}">
                    <span class="pd-rating-label small">{{ $label }}</span>
                    <div class="flex-grow-1"><div class="pd-hbar bg-primary" style="width: {{ $count / $maxRating * 100 }}%"></div></div>
                    <span class="small fw-semibold pd-count">{{ $count }}</span>
                </div>
            @endforeach
        @endif
    </div>
</div>

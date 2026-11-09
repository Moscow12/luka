{{--
    Achievement bar with a tick for where the goal should be by today.
    Expects: $progress (?float), $status (string), $expected (float)
--}}
@php
    $meta = \App\Services\Performance\PlanProgress::STATUSES[$status] ?? \App\Services\Performance\PlanProgress::STATUSES['no_data'];
    $label = $progress === null
        ? 'No measurable target'
        : number_format($progress, 1).'% achieved, '.number_format($expected, 0).'% expected by today';
@endphp
<div class="d-flex align-items-center gap-2" title="{{ $label }}">
    <div class="pd-track flex-grow-1 bg-{{ $meta['color'] }}-subtle"
         role="meter" aria-label="{{ $label }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress ?? 0 }}">
        @if($progress !== null)
            <div class="pd-fill bg-{{ $meta['color'] }}" style="width: {{ max($progress, 0) }}%"></div>
        @endif
        <span class="pd-marker" style="left: {{ $expected }}%"></span>
    </div>
    <span class="pd-pct small fw-semibold">{{ $progress === null ? '—' : number_format($progress, 0).'%' }}</span>
</div>

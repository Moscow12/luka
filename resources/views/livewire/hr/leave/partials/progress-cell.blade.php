{{-- Compact approval progress for a table cell: one segment per level, then who it waits on. Expects: $leave with ->progress --}}
@php
    $p = $leave->progress;
    $current = collect($p['steps'])->first(fn ($step) => in_array($step['state'], ['current', 'blocked']));
@endphp
@if($p['total'] > 0)
    <div class="d-flex gap-1 mb-1" role="img" aria-label="{{ $p['done'] }} of {{ $p['total'] }} approvals done">
        @for($i = 0; $i < $p['total']; $i++)
            <span class="flex-fill rounded-pill
                {{ $i < $p['done'] ? 'bg-success' : ($p['state'] === 'rejected' && $i === $p['done'] ? 'bg-danger' : ($current && $i === $p['done'] ? 'bg-warning' : 'bg-secondary-subtle')) }}"
                style="height: 6px;"></span>
        @endfor
    </div>
@endif
<small class="{{ $p['state'] === 'blocked' ? 'text-danger-emphasis' : 'text-muted' }}">
    @if($current)
        <i class="fa-solid {{ $current['state'] === 'blocked' ? 'fa-triangle-exclamation' : 'fa-hourglass-half' }} me-1"></i>
        {{ $current['title'] }}@if(!empty($current['approvers'])): {{ implode(', ', $current['approvers']) }}@endif
    @elseif($p['state'] === 'approved')
        <i class="fa-solid fa-circle-check text-success me-1"></i>All approvals done
    @elseif($p['state'] === 'rejected')
        <i class="fa-solid fa-circle-xmark text-danger me-1"></i>{{ $p['summary'] }}
    @else
        {{ $p['summary'] }}
    @endif
</small>

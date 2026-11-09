{{-- Schedule status chip: colour is always paired with an icon and a label. Expects: $status --}}
@php($meta = \App\Services\Performance\PlanProgress::STATUSES[$status] ?? \App\Services\Performance\PlanProgress::STATUSES['no_data'])
<span class="badge rounded-pill bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}-emphasis border border-{{ $meta['color'] }}-subtle text-nowrap">
    <i class="fa-solid {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
</span>

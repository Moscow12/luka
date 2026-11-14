@props(['label'])

{{-- One label / value pair of a read-only detail grid --}}
<div {{ $attributes->merge(['class' => 'col-6 col-md-4 col-lg-3']) }}>
    <div class="text-muted small">{{ $label }}</div>
    <div class="fw-semibold text-break">{{ $slot }}</div>
</div>

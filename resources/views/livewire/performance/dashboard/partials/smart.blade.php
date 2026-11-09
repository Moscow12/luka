{{-- One chip per SMART letter; a missed criterion is struck through so it reads without colour. Expects: $smart --}}
@php($names = ['S' => 'Specific: has a description', 'M' => 'Measurable: numeric target and unit', 'A' => 'Achievable: target within its acceptable range', 'R' => 'Relevant: linked to a goal one level up', 'T' => 'Time-bound: belongs to a dated plan'])
<span class="d-inline-flex gap-1" aria-label="SMART check">
    @foreach($smart as $letter => $met)
        <span class="pd-smart {{ $met ? 'pd-smart-ok' : 'pd-smart-miss' }}" title="{{ $names[$letter] }} — {{ $met ? 'met' : 'missing' }}">{{ $letter }}</span>
    @endforeach
</span>

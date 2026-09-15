@php
    $user = auth()->user();
    $employee = \App\Models\Employee::where('user_id', $user?->id)->with('workstation')->first();
    $workstation = $employee?->workstation;
    $logo = $workstation?->logo;
    $workstationName = $workstation?->workstation_name ?? 'HRP';
@endphp

<div class="brand-logo">
    <a class="d-none d-md-flex align-items-center gap-2" href="{{ route('dashboard') }}">
        @if($logo)
            <img src="{{ asset('storage/' . $logo) }}"
                alt="{{ $workstationName }}"
                class="rounded"
                style="height: 36px; width: auto; max-width: 140px; object-fit: contain;" />
        @else
            <img src="{{ asset('images/brand/logo/logo-icon.svg') }}" alt="HRP" />
        @endif
        <span class="fw-bold fs-5 site-logo-text text-truncate" style="max-width: 120px;">
            {{ $workstationName }}
        </span>
    </a>
</div>

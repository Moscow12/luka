@props(['title', 'subtitle' => null])

{{-- Standard engineering page: heading, action buttons, flash messages and the alert styles --}}
<div>
    @once
        <style>
            /* 1s pulse for "act now" alerts; solid colour when the user prefers reduced motion */
            @keyframes eng-pulse { 50% { opacity: .35; } }
            .eng-blink { animation: eng-pulse 1s ease-in-out infinite; }
            .eng-row-alert { box-shadow: inset 4px 0 0 var(--bs-danger); }
            .eng-row-rework { box-shadow: inset 4px 0 0 var(--bs-danger); background-color: rgba(var(--bs-danger-rgb), .06); }
            .eng-row-emergency { box-shadow: inset 4px 0 0 var(--bs-danger); }
            .eng-tile { transition: transform .12s ease, box-shadow .12s ease; cursor: pointer; }
            .eng-tile:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08); }
            .eng-step { width: 1.75rem; height: 1.75rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: .85rem; }
            .eng-cell-green { background-color: rgba(25, 135, 84, .18) !important; }
            .eng-cell-yellow { background-color: rgba(255, 193, 7, .25) !important; }
            .eng-cell-red { background-color: rgba(220, 53, 69, .2) !important; }
            @media (prefers-reduced-motion: reduce) { .eng-blink { animation: none; } }
            @media print {
                #miniSidebar, .offcanvasNav, .navbar, .eng-no-print, .chat-launcher { display: none !important; }
                #content { margin: 0 !important; padding: 0 !important; }
                .card { border: 0 !important; box-shadow: none !important; }
            }
        </style>
    @endonce

    <div class="row mb-4 eng-no-print">
        <div class="col-12 d-md-flex justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1">{{ $title }}</h4>
                @if($subtitle)<p class="text-muted mb-0">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">{{ $actions }}</div>
            @endisset
        </div>
    </div>

    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $colour)
        @if(session()->has($key))
            <div class="alert alert-{{ $colour }} alert-dismissible fade show eng-no-print" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{ $slot }}
</div>

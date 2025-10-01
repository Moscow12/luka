{{-- resources/views/components/forms/button.blade.php --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'outline' => false,
    'size' => null,
    'block' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'loadingText' => null,
    'loadingTarget' => null,
])

@php
    $buttonClass = 'btn';

    // Add variant class
    if ($outline) {
        $buttonClass .= ' btn-outline-' . $variant;
    } else {
        $buttonClass .= ' btn-' . $variant;
    }

    // Add size class
    if ($size === 'sm') {
        $buttonClass .= ' btn-sm';
    } elseif ($size === 'lg') {
        $buttonClass .= ' btn-lg';
    }

    // Add width class for block
    if ($block) {
        $buttonClass .= ' w-100';
    }
@endphp

@if($block)
<div class="d-grid">
@endif
    <button
        type="{{ $type }}"
        class="{{ $buttonClass }}"
        @if($disabled) disabled @endif
        @if($loadingText) wire:loading.attr="disabled" @endif
        {{ $attributes }}
    >
        @if($loadingText)
            {{-- Default state (not loading) --}}
            <span wire:loading.remove @if($loadingTarget) wire:target="{{ $loadingTarget }}" @endif>
                @if($icon && $iconPosition === 'left')
                    <span class="me-2">{!! $icon !!}</span>
                @endif

                {{ $slot }}

                @if($icon && $iconPosition === 'right')
                    <span class="ms-2">{!! $icon !!}</span>
                @endif
            </span>

            {{-- Loading state --}}
            <span wire:loading @if($loadingTarget) wire:target="{{ $loadingTarget }}" @endif>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                {{ $loadingText }}
            </span>
        @else
            {{-- No loading state --}}
            @if($icon && $iconPosition === 'left')
                <span class="me-2">{!! $icon !!}</span>
            @endif

            {{ $slot }}

            @if($icon && $iconPosition === 'right')
                <span class="ms-2">{!! $icon !!}</span>
            @endif
        @endif
    </button>
@if($block)
</div>
@endif

@props(['title', 'close', 'size' => '', 'header' => ''])

{{-- Server-driven modal: rendered only while its state is set; "close" is the Livewire action that clears it --}}
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);" wire:keydown.escape.window="{{ $close }}">
    <div class="modal-dialog modal-dialog-scrollable {{ $size }}">
        <div class="modal-content">
            <div class="modal-header {{ $header }}">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close {{ $header ? 'btn-close-white' : '' }}" wire:click="{{ $close }}" aria-label="Close"></button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>

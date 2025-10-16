<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog {{ $centered ? 'modal-dialog-centered' : '' }} {{ $size }}">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

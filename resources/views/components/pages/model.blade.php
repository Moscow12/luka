@props(['title', 'formaction', 'modalMode', 'showModal', 'size' => ''])

<!-- Modal -->
<div>
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
        @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog {{ $size ? 'modal-' . $size : '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $title }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="{{ $formaction }}">
                    @csrf
                    <div class="modal-body">
                        {{ $slot }}
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">close</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $modalMode === 'edit' ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
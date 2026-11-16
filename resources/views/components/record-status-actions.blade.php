@props([
    'active' => true,   // current status
    'inUse' => false,   // referenced by other records: no delete, only activate / deactivate
    'toggle',           // Livewire action, e.g. "toggleStatus('uuid')"
    'delete' => null,   // Livewire action; omit to never offer delete
    'confirm' => 'Delete this record?', // null when the screen has its own confirmation dialog
])

{{-- Records already used elsewhere are deactivated instead of deleted --}}
<button type="button" class="btn btn-sm {{ $active ? 'btn-outline-secondary' : 'btn-outline-success' }}" wire:click="{{ $toggle }}"
    title="{{ $active ? 'Deactivate: hide from lists for new records' : 'Activate' }}">
    <i class="fa-solid {{ $active ? 'fa-ban' : 'fa-check' }}"></i> {{ $active ? 'Deactivate' : 'Activate' }}
</button>
@if($delete && ! $inUse)
    <button type="button" class="btn btn-sm btn-danger" wire:click="{{ $delete }}" @if($confirm) wire:confirm="{{ $confirm }}" @endif>
        <i class="fa-solid fa-trash"></i> Delete
    </button>
@endif

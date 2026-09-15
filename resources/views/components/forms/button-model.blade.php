@props(['name', 'classbtn' => 'fa-solid fa-plus'])

<button
    class="btn btn-primary btn-sm d-flex flex-row gap-1 align-items-center"
    @if(!$attributes->has('wire:click'))
        wire:click="openModal('create')"
    @endif
    {{ $attributes }}
>
    <i class="{{ $classbtn }}"></i>
    {{ $name }}
</button>
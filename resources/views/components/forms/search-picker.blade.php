@props([
    'name',
    'label' => null,
    'required' => false,
    'selected' => null,
    'items' => collect(),
    'searchValue' => '',
    'showDropdown' => false,
    'searchProp' => 'search',
    'dropdownProp' => 'showDropdown',
    'searchPlaceholder' => 'Search...',
    'emptyText' => 'No results found',
    'disabled' => false,
    'disabledText' => null,
    'selectMethod' => 'selectItem',
    'clearMethod' => 'clearItem',
    'labelKey' => 'name',
    'sublabelKey' => null,
    'allowCreate' => false,
    'createMode' => false,
    'createButtonText' => 'Add new',
    'startCreateMethod' => 'startCreate',
    'zIndex' => 1056,
])

@php
    $errorKey = $name;
    $resolveLabel = fn ($item) => is_object($item)
        ? (method_exists($item, 'getFullName') && $labelKey === 'name' ? $item->getFullName() : data_get($item, $labelKey))
        : data_get($item, $labelKey);
    $resolveSub = fn ($item) => $sublabelKey
        ? (is_object($item) ? data_get($item, $sublabelKey) : data_get($item, $sublabelKey))
        : null;
@endphp

<div wire:key="search-picker-{{ $name }}">
    @if($label)
        <label class="form-label">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($selected)
        {{-- Selected state: show chip with Change button --}}
        <div class="d-flex align-items-center justify-content-between border rounded p-2">
            <span>
                {{ $resolveLabel($selected) }}
                @if($resolveSub($selected))
                    <small class="text-muted">({{ $resolveSub($selected) }})</small>
                @endif
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="{{ $clearMethod }}">
                <i class="fa-solid fa-pen me-1"></i> Change
            </button>
        </div>
    @elseif($allowCreate && $createMode)
        {{-- Inline create slot --}}
        <div class="border rounded p-2">
            {{ $create ?? '' }}
        </div>
    @else
        {{-- Search input + dropdown --}}
        <div class="position-relative">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text"
                       class="form-control @error($errorKey) is-invalid @enderror"
                       placeholder="{{ $disabled && $disabledText ? $disabledText : $searchPlaceholder }}"
                       wire:model.live.debounce.300ms="{{ $searchProp }}"
                       wire:focus="$set('{{ $dropdownProp }}', true)"
                       @if($disabled) disabled @endif
                       autocomplete="off" />
            </div>

            @if(! $disabled && ($showDropdown || strlen($searchValue ?? '')))
                <div class="border rounded mt-1 bg-white shadow-sm position-absolute w-100"
                     style="z-index: {{ $zIndex }}; max-height: 240px; overflow-y: auto;">
                    @forelse($items as $item)
                        <button type="button"
                                class="dropdown-item d-flex justify-content-between px-3 py-2 text-wrap"
                                wire:key="picker-{{ $name }}-{{ $item->id ?? $loop->index }}"
                                wire:click="{{ $selectMethod }}('{{ $item->id ?? $loop->index }}')">
                            <span>{{ $resolveLabel($item) }}</span>
                            @if($resolveSub($item))
                                <small class="text-muted">{{ $resolveSub($item) }}</small>
                            @endif
                        </button>
                    @empty
                        <div class="px-3 py-2 text-muted small">{{ $emptyText }}</div>
                    @endforelse

                    @if($allowCreate)
                        <div class="border-top px-3 py-2 bg-light">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                    wire:click="{{ $startCreateMethod }}">
                                <i class="fa-solid fa-plus me-1"></i>
                                {{ $createButtonText }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            @error($errorKey)
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    @endif
</div>

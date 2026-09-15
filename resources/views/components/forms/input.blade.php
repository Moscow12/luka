{{-- resources/views/components/forms/input.blade.php --}}
@props([
'type' => 'text',
'name',
'label' => null,
'value' => '',
'placeholder' => '',
'required' => false,
'autofocus' => false,
'disabled' => false,
'readonly' => false,
'min' => null,
'max' => null,
'step' => null,
'colSm' => '12',
'colMd' => null,
'colLg' => null,
'wrapper' => true,
'options' => [], {{-- for select --}}
'rows' => 3, {{-- for textarea --}}
])

@php
$colClasses = "col-sm-{$colSm}";
if ($colMd) $colClasses .= " col-md-{$colMd}";
if ($colLg) $colClasses .= " col-lg-{$colLg}";

// Detect Livewire model binding dynamically (wire:model, wire:model.defer, etc.)
$wireModel = collect($attributes->getAttributes())
->keys()
->first(fn($attr) => str_starts_with($attr, 'wire:model'));
@endphp

@if($wrapper)
<div class="{{ $colClasses }}">
    @endif

    <div {{ $attributes->only('class')->merge(['class' => 'form-floating mb-3']) }}>

        {{-- TEXTAREA --}}
        @if($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            placeholder="{{ $placeholder ?: ($label ?? ucfirst($name)) }}"
            rows="{{ $rows }}"
            class="form-control @error($name) is-invalid @enderror"
            {{ $attributes->except(['class']) }}
            @if(!$wireModel) wire:model.defer="{{ $name }}" @endif
            @if($required) required @endif
            @if($autofocus) autofocus @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif>{{ old($name, $value) }}</textarea>

        {{-- SELECT --}}
        @elseif($type === 'select')
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            class="form-select @error($name) is-invalid @enderror"
            {{ $attributes->except(['class']) }}
            @if(!$wireModel) wire:model.defer="{{ $name }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif>
            <option value="">Select {{ strtolower($label ?? ucfirst($name)) }}</option>
            @foreach($options as $key => $option)
            <option value="{{ $key }}">{{ $option }}</option>
            @endforeach
        </select>

        {{-- DEFAULT INPUT --}}
        @else
        <input
            type="{{ $type === 'date' || $type === 'time' || $type === 'datetime' ? 'text' : $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder ?: ($label ?? ucfirst($name)) }}"
            class="form-control @if($type === 'date') flatpickr @elseif($type === 'time') timepickr @elseif($type === 'datetime') datetimepickr @endif @error($name) is-invalid @enderror"
            {{ $attributes->except(['class']) }}
            @if(!$wireModel)
            @if($type==='file' )
            wire:model="{{ $name }}"
            @else
            wire:model.defer="{{ $name }}"
            @endif
            @endif
            @if($required) required @endif
            @if($autofocus) autofocus @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($min !==null && $type !== 'date' && $type !== 'time' && $type !== 'datetime') min="{{ $min }}" @endif
            @if($max !==null && $type !== 'date' && $type !== 'time' && $type !== 'datetime') max="{{ $max }}" @endif
            @if($step !==null) step="{{ $step }}" @endif />

        @endif

        @if($label)
        <label for="{{ $name }}">{{ $label }}</label>
        @endif

        @error($name)
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    @if($wrapper)
</div>
@endif
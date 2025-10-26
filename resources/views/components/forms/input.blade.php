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
    'options' => [],  {{-- For <select> inputs --}}
    'rows' => 3,      {{-- For <textarea> --}}
])

@php
    $colClasses = "col-sm-{$colSm}";
    if ($colMd) $colClasses .= " col-md-{$colMd}";
    if ($colLg) $colClasses .= " col-lg-{$colLg}";
@endphp

@if($wrapper)
<div class="{{ $colClasses }}">
@endif
    <div {{ $attributes->only('class')->merge(['class' => 'form-floating mb-3']) }}>
        
        {{-- Handle input types --}}
        @if($type === 'textarea')
            <textarea
                name="{{ $name }}"
                id="{{ $name }}"
                placeholder="{{ $placeholder ?: ($label ?? ucfirst($name)) }}"
                rows="{{ $rows }}"
                class="form-control @error($name) is-invalid @enderror"
                @if($required) required @endif
                @if($autofocus) autofocus @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
            >{{ old($name, $value) }}</textarea>

        @elseif($type === 'select')
            <select
                name="{{ $name }}"
                id="{{ $name }}"
                class="form-select @error($name) is-invalid @enderror"
                @if($required) required @endif
                @if($disabled) disabled @endif
            >
                <option value="">Select {{ strtolower($label ?? ucfirst($name)) }}</option>
                @foreach($options as $key => $option)
                    <option value="{{ $key }}" {{ old($name, $value) == $key ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder ?: ($label ?? ucfirst($name)) }}"
                class="form-control @error($name) is-invalid @enderror"
                @if($required) required @endif
                @if($autofocus) autofocus @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
                @if($min !== null) min="{{ $min }}" @endif
                @if($max !== null) max="{{ $max }}" @endif
                @if($step !== null) step="{{ $step }}" @endif
            />
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

{{-- resources/views/components/forms/checkbox.blade.php --}}
@props([
    'name',
    'label',
    'value' => '',
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'wrapper' => true,
])

@if($wrapper)
<div class="form-check">
@endif
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        class="form-check-input @error($name) is-invalid @enderror" {{ old($name, $checked) ? 'checked' : '' }}
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes }}
    />
    <label class="form-check-label" for="{{ $name }}">
        {{ $label }}
    </label>

    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
@if($wrapper)
</div>
@endif

			
	
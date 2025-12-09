@props([
    'label' => null,
    'name',
    'options' => [], // Can be associative or indexed
    'model' => null,
    'placeholder' => 'Select an option',
    'showPlaceholder' => true,
    'required' => false,
    'titleCase' => true,
])

@php
    use Illuminate\Support\Str;
    // Detect if options are associative (value => label) or simple (label only)
    $isAssoc = array_keys($options) !== range(0, count($options) - 1);
@endphp

{{-- Label --}}
@if ($label)
    <label for="{{ $name }}" class="form-label fw-semibold">
        @if ($required)
            <span style="color: red">*</span>
        @endif
        {{ $label }}
    </label>
@endif

{{-- Select input --}}
<select name="{{ $name }}" id="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'form-select ' . ($errors->has($name) ? 'is-invalid' : ''),
    ]) }}
    @if ($model) wire:model="{{ $model }}" @endif @required($required)>
    {{-- Placeholder --}}
    @if ($showPlaceholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    {{-- Options --}}
    @foreach ($options as $key => $option)
        <option value="{{ $isAssoc ? $key : $option }}">
            @if ($titleCase)
                {{ $isAssoc ? $option : Str::title($option) }}
            @else
                {{ $isAssoc ? $option : $option }}
            @endif
        </option>
    @endforeach
</select>

{{-- Validation feedback --}}
@error($name)
    <small style="color: rgb(205, 56, 56)">{{ $message }}</small>
@enderror

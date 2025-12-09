@props([
    'label' => null,
    'type' => 'text',
    'name',
    'placeholder' => '',
    'model' => null,
    'required' => false,
])

@php
    use Illuminate\Support\Str;
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

{{-- Input --}}
<input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
        'class' => 'form-control mb-0' . ($errors->has($name) ? 'is-invalid' : ''),
    ]) }}
    @if ($model) wire:model="{{ $model }}" @endif @required($required)
    style="margin-bottom: 0px">

{{-- Validation message --}}
@error($name)
    <div class="text-danger small mt-2">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $message }}
    </div>
@enderror

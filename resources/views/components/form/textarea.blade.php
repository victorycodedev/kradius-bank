@props([
    'label' => null,
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

<textarea name="{{ $name }}" placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
        'class' => 'form-control mb-0',
    ]) }}
    @if ($model) wire:model="{{ $model }}" @endif @required($required)
    style="margin-bottom: 0px"></textarea>

{{-- Validation message --}}
@error($name)
    @guest
        <small style="color: rgb(205, 56, 56)">{{ $message }}</small>
    @endguest
    @auth
        <div class="text-danger small mt-2">
            <i class="fa-solid fa-circle-exclamation me-1"></i>
            {{ $message }}
        </div>
    @endauth
@enderror

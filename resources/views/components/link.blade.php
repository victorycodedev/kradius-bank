@props([
    'href' => '#',
    'icon' => null,
    'method' => null,
    'active' => false,
])

@php
    $classes = 'text-decoration-none'; // base styles

    if ($active) {
        $classes .= ' active'; // add active class if true
    }
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
    @if ($icon)
        <i class="bi bi-{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</a>

@props([
    'size' => 'md',
    'variant' => 'default',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 sm:h-9 sm:w-9',
        'lg' => 'h-10 w-10',
        default => 'h-9 w-9',
    };

    $class = $attributes->get('class')
        ? $attributes->get('class')
        : $sizeClasses.' shrink-0 rounded-xl object-contain';

    $logoUrl = $store['logoUrl'] ?? asset('images/store-logo.svg');
@endphp

<img
    src="{{ $logoUrl }}"
    alt="{{ $store['name'] }}"
    {{ $attributes->merge(['class' => $class]) }}
>

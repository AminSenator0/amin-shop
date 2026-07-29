@props(['status'])

@php
    $color = $status->color();
    $class = match ($color) {
        'amber', 'yellow' => 'admin-badge-warning',
        'emerald', 'green' => 'admin-badge-success',
        'rose', 'red' => 'admin-badge-danger',
        'blue' => 'admin-badge-info',
        'indigo' => 'admin-badge-indigo',
        'purple' => 'admin-badge-purple',
        default => 'admin-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $status->label() }}</span>

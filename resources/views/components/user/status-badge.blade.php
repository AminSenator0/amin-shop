@props(['status'])

@php
    $color = $status->color();
    $tone = match ($color) {
        'amber', 'yellow' => 'is-amber',
        'emerald', 'green' => 'is-emerald',
        'rose', 'red' => 'is-rose',
        'blue' => 'is-blue',
        'indigo' => 'is-indigo',
        'purple' => 'is-purple',
        default => 'is-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "user-status-badge {$tone}"]) }}>
    {{ $status->label() }}
</span>

@props(['type' => 'info', 'title', 'actionUrl' => null, 'actionLabel' => null])

@php
    $classes = match ($type) {
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        default => 'border-shop-primary/20 bg-shop-primary/5 text-shop-text',
    };
@endphp

<div {{ $attributes->merge(['class' => "mb-6 flex flex-wrap items-center justify-between gap-4 rounded-3xl border px-5 py-4 {$classes}"]) }}>
    <div class="flex items-start gap-3">
        @if(isset($icon))
            <div class="mt-0.5 shrink-0">{{ $icon }}</div>
        @endif
        <div>
            <p class="font-black">{{ $title }}</p>
            @if(isset($description))
                <p class="mt-1 text-sm opacity-80">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="user-action-chip shrink-0 {{ $type === 'danger' ? 'is-primary' : '' }}">{{ $actionLabel }}</a>
    @endif
</div>

@props(['text' => '', 'limit' => 200, 'variant' => 'admin'])

@php
    $textClass = match ($variant) {
        'shop' => 'text-sm text-shop-muted leading-relaxed whitespace-pre-wrap',
        default => 'text-sm text-zinc-600 leading-relaxed whitespace-pre-wrap',
    };
    $btnClass = match ($variant) {
        'shop' => 'mt-1.5 text-xs font-bold text-shop-primary transition hover:text-shop-text',
        default => 'mt-1.5 text-xs font-bold text-indigo-600 transition hover:text-indigo-800',
    };
    $needsExpand = mb_strlen($text) > $limit;
    $preview = $needsExpand ? mb_substr($text, 0, $limit).'…' : $text;
@endphp

@if($text)
    <div x-data="{ expanded: false }" {{ $attributes }}>
        @if($needsExpand)
            <p class="{{ $textClass }}" x-show="!expanded">{{ $preview }}</p>
            <p class="{{ $textClass }}" x-show="expanded" x-cloak>{{ $text }}</p>
            <button type="button" @click="expanded = !expanded" class="{{ $btnClass }}" x-text="expanded ? 'نمایش کمتر' : 'ادامه مطلب'"></button>
        @else
            <p class="{{ $textClass }}">{{ $text }}</p>
        @endif
    </div>
@endif

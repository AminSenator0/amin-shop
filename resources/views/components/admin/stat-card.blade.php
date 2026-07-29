@props(['label', 'value', 'icon' => null, 'color' => 'default', 'href' => null, 'trend' => null, 'subtext' => null])

@php
    $colorClasses = match($color) {
        'emerald' => 'admin-stat-emerald',
        'blue' => 'admin-stat-blue',
        'violet' => 'admin-stat-violet',
        'amber' => 'admin-stat-amber',
        'rose' => 'admin-stat-rose',
        default => 'admin-stat-default',
    };
@endphp

@if($href)
    <a href="{{ $href }}" class="admin-stat-card {{ $colorClasses }} group">
@else
    <div class="admin-stat-card {{ $colorClasses }}">
@endif
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="admin-stat-value">{{ $value }}</p>
            <p class="admin-stat-label">{{ $label }}</p>
            @if($subtext)
                <p class="mt-1 text-[11px] text-zinc-500">{{ $subtext }}</p>
            @endif
            @if($trend !== null)
                @php
                    $trendUp = $trend > 0;
                    $trendFlat = $trend == 0;
                    $trendClass = $trendUp ? 'text-emerald-600' : ($trendFlat ? 'text-zinc-500' : 'text-rose-600');
                    $trendPrefix = $trendUp ? '↑' : ($trendFlat ? '—' : '↓');
                @endphp
                <p class="mt-1 text-[11px] font-bold {{ $trendClass }}">
                    {{ $trendPrefix }} {{ format_number(abs($trend)) }}٪ نسبت به دیروز
                </p>
            @endif
        </div>
        @if($icon)
            <div class="admin-stat-icon">{!! $icon !!}</div>
        @endif
    </div>
@if($href)
    </a>
@else
    </div>
@endif

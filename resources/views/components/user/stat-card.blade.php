@props(['href', 'value', 'label', 'hint' => null])

<a href="{{ $href }}" class="user-stat-card block">
  @isset($icon)
    <div class="user-stat-icon">{{ $icon }}</div>
  @endisset
    <p class="text-3xl font-black text-shop-primary">{{ $value }}</p>
    <p class="mt-1 text-sm font-bold text-shop-muted">{{ $label }}</p>
    @if($hint)
        <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $hint }}</p>
    @endif
</a>

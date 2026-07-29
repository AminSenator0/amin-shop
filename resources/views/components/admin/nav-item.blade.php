@props(['href', 'active' => false, 'icon', 'badge' => null, 'label' => null])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'admin-nav-item' . ($active ? ' admin-nav-item-active' : '')]) }}
   data-nav-label="{{ $label ?? $slot }}"
   @if($active) aria-current="page" @endif>
    <span class="admin-nav-icon" aria-hidden="true">{!! $icon !!}</span>
    <span class="truncate flex-1">{{ $slot }}</span>
    @if($badge)
        <span class="admin-nav-badge">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>

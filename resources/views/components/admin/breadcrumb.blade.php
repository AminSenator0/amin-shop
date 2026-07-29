@props(['current' => 'داشبورد'])

@php
    $items = \App\Support\AdminBreadcrumb::items($current);
    $group = \App\Support\AdminBreadcrumb::group();
@endphp

@if($group)
    <p class="admin-breadcrumb-group">{{ $group }}</p>
@endif

<nav class="admin-breadcrumb" aria-label="مسیر صفحه">
    @foreach($items as $index => $item)
        @if($index > 0)
            <svg class="admin-breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        @endif

        @if($item['url'] && $index < count($items) - 1)
            <a href="{{ $item['url'] }}" class="admin-breadcrumb-link">{{ $item['label'] }}</a>
        @else
            <span class="admin-breadcrumb-current" @if($index === count($items) - 1) aria-current="page" @endif>{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>

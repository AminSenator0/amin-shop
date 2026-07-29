@props(['placeholder' => 'جستجو...'])

@php
    $hasActiveFilters = count(request()->query()) > 0;
@endphp

<form method="GET" {{ $attributes->merge(['class' => 'admin-search-form']) }}>
    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}" class="admin-input admin-search-input">
    {{ $slot }}
    <button type="submit" class="admin-btn-secondary admin-search-btn">جستجو</button>
    @if($hasActiveFilters)
        <a href="{{ url()->current() }}" class="admin-btn-secondary admin-search-btn admin-search-clear">حذف فیلتر</a>
    @endif
</form>

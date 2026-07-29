@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'user-page-header']) }}>
    <h1 class="text-2xl font-black leading-tight text-shop-text sm:text-3xl">{{ $title }}</h1>
    @if($subtitle)
        <p class="mt-2 text-sm leading-7 text-shop-muted">{{ $subtitle }}</p>
    @endif
</div>

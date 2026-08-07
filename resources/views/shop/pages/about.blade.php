@extends('layouts.shop')

@section('title', 'درباره ما | ' . $store['name'])

@section('meta_description', 'آشنایی با ' . $store['name'] . ' — ' . ($store['tagline'] ?? 'فروشگاه آنلاین با بهترین محصولات و ارسال سریع'))

@section('canonical', route('pages.about'))

@section('open_graph')
    <meta property="og:type" content="website">
    <meta property="og:title" content="درباره ما | {{ $store['name'] }}">
    <meta property="og:description" content="{{ $store['tagline'] ?? 'آشنایی با ' . $store['name'] }}">
    <meta property="og:url" content="{{ route('pages.about') }}">
@endsection

@section('twitter_card')
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="درباره ما | {{ $store['name'] }}">
    <meta name="twitter:description" content="{{ $store['tagline'] ?? 'آشنایی با ' . $store['name'] }}">
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'AboutPage',
    'name' => 'درباره ' . $store['name'],
    'url' => route('pages.about'),
    'description' => $store['tagline'] ?? 'آشنایی با ' . $store['name'],
    'mainEntity' => [
        '@type' => 'Organization',
        'name' => $store['name'],
        'description' => $store['aboutContent'] ? strip_tags($store['aboutContent']) : null,
        'url' => route('home'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="card p-8 sm:p-10">
        <span class="badge mb-4">درباره ما</span>
        <h1 class="text-2xl font-black text-shop-text sm:text-3xl">درباره {{ $store['name'] }}</h1>
        @if($store['tagline'])
            <p class="mt-2 text-sm text-shop-muted">{{ $store['tagline'] }}</p>
        @endif
        <div class="prose-shop mt-8 space-y-4 text-sm leading-8 text-shop-muted">
            @foreach(preg_split('/\n\s*\n/', trim($store['aboutContent'])) as $paragraph)
                @if(str_starts_with(trim($paragraph), '•'))
                    <ul class="list-none space-y-2">
                        @foreach(explode("\n", trim($paragraph)) as $item)
                            <li class="flex items-start gap-2">
                                <svg class="mt-1 h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ ltrim(trim($item), '•') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ trim($paragraph) }}</p>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection

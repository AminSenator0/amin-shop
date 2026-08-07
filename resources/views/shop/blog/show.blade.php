@extends('layouts.shop')

@section('title', $post->title . ' | وبلاگ ' . $store['name'])

@section('meta_description', $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160))

@section('canonical', route('blog.show', $post->slug))

@section('open_graph')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}">
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    <meta property="og:image" content="{{ $post->imageUrl() }}">
    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() ?? $post->created_at->toIso8601String() }}">
    <meta property="article:author" content="{{ $store['name'] }}">
@endsection

@section('twitter_card')
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->title }}">
    <meta name="twitter:description" content="{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}">
    <meta name="twitter:image" content="{{ $post->imageUrl() }}">
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post->title,
    'image' => $post->imageUrl(),
    'datePublished' => $post->published_at?->toIso8601String() ?? $post->created_at->toIso8601String(),
    'dateModified' => $post->updated_at->toIso8601String(),
    'author' => [
        '@type' => 'Organization',
        'name' => $store['name'],
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $store['name'],
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('images/logo.png'),
        ],
    ],
    'description' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 300),
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('blog.show', $post->slug),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'وبلاگ', 'item' => route('blog.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('blog.show', $post->slug)],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route('blog.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-bold text-shop-primary hover:underline">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        بازگشت به مقالات
    </a>

    <div class="card overflow-hidden">
        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="aspect-[16/9] w-full object-cover">
        <div class="p-8 sm:p-10">
            <time class="text-sm text-shop-muted">{{ $post->published_at ? format_jalali($post->published_at) : '' }}</time>
            <h1 class="mt-3 text-2xl font-black text-shop-text sm:text-3xl">{{ $post->title }}</h1>
            @if($post->excerpt)
                <p class="mt-4 text-base font-medium text-shop-muted">{{ $post->excerpt }}</p>
            @endif
            <div class="prose-shop mt-8 space-y-4 text-sm leading-8 text-shop-muted">
                @foreach(preg_split('/\n\s*\n/', trim($post->content)) as $paragraph)
                    <p>{{ trim($paragraph) }}</p>
                @endforeach
            </div>
        </div>
    </div>

    @if($relatedPosts->isNotEmpty())
        <div class="mt-12">
            <h2 class="mb-6 text-lg font-black text-shop-text">مقالات مرتبط</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($relatedPosts as $related)
                    <a href="{{ route('blog.show', $related->slug) }}" class="card block p-4 transition hover:shadow-lg">
                        <h3 class="line-clamp-2 font-bold text-shop-text hover:text-shop-primary">{{ $related->title }}</h3>
                        <p class="mt-2 text-xs text-shop-muted">{{ $related->published_at ? format_jalali($related->published_at) : '' }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>
@endsection

@extends('layouts.shop')

@section('title', 'مقالات و راهنمای خرید | وبلاگ ' . $store['name'])

@section('meta_description', 'مقالات، نکات خرید، تخفیف‌ها و راهنمای استفاده از محصولات در وبلاگ ' . $store['name'])

@section('canonical', route('blog.index'))

@section('open_graph')
    <meta property="og:type" content="website">
    <meta property="og:title" content="مقالات و راهنمای خرید | وبلاگ {{ $store['name'] }}">
    <meta property="og:description" content="مقالات، نکات خرید و راهنمای محصولات {{ $store['name'] }}">
    <meta property="og:url" content="{{ route('blog.index') }}">
@endsection

@section('twitter_card')
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="مقالات و راهنمای خرید | وبلاگ {{ $store['name'] }}">
    <meta name="twitter:description" content="مقالات، نکات خرید و راهنمای محصولات {{ $store['name'] }}">
@endsection

@section('pagination_seo')
    @if($posts->currentPage() > 1)
        <link rel="prev" href="{{ $posts->previousPageUrl() }}">
    @endif
    @if($posts->hasMorePages())
        <link rel="next" href="{{ $posts->nextPageUrl() }}">
    @endif
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'وبلاگ ' . $store['name'],
    'url' => route('blog.index'),
    'description' => 'مقالات، نکات خرید و راهنمای محصولات ' . $store['name'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-10">
        <span class="badge mb-2">بلاگ</span>
        <h1 class="section-title">مقالات و راهنماها</h1>
        <p class="mt-2 text-sm text-shop-muted">نکات خرید، تخفیف‌ها و راهنمای استفاده از محصولات</p>
    </div>

    @if($posts->isNotEmpty())
        <div class="home-blog-grid">
            @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="home-blog-card group">
                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy" class="home-blog-image">
                    <div class="home-blog-body">
                        <time class="home-blog-date">{{ $post->published_at ? format_jalali($post->published_at) : '' }}</time>
                        <h2 class="home-blog-title group-hover:text-shop-primary">{{ $post->title }}</h2>
                        <p class="home-blog-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 140) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        @if($posts->hasPages())
            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    @else
        <div class="card py-16 text-center text-shop-muted">هنوز مقاله‌ای منتشر نشده است.</div>
    @endif
</section>
@endsection

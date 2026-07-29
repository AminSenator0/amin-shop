@extends('layouts.shop')

@section('title', 'مقالات — '.$store['name'])

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

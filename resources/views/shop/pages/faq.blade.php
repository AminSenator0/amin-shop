@extends('layouts.shop')

@section('title', 'سوالات متداول — '.$store['name'])

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
        <span class="badge mb-2">راهنما</span>
        <h1 class="section-title">سوالات متداول</h1>
        <p class="mt-2 text-sm text-shop-muted">پاسخ سوالات رایج مشتریان</p>
    </div>

    @if($faqs->isNotEmpty())
        <div class="home-faq-list" x-data="{ open: null }">
            @foreach($faqs as $faq)
                <div class="home-faq-item">
                    <button type="button" class="home-faq-question" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }}">
                        <span>{{ $faq->question }}</span>
                        <svg class="h-5 w-5 shrink-0 transition-transform" :class="open === {{ $faq->id }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open === {{ $faq->id }}" x-cloak class="home-faq-answer">{{ $faq->answer }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card py-16 text-center text-shop-muted">سوالی ثبت نشده است.</div>
    @endif
</div>
@endsection

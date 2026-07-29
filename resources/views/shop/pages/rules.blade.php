@extends('layouts.shop')

@section('title', 'قوانین و مقررات — '.$store['name'])

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="card p-8 sm:p-10">
        <span class="badge mb-4">قوانین</span>
        <h1 class="text-2xl font-black text-shop-text sm:text-3xl">قوانین و مقررات فروشگاه</h1>
        <div class="prose-shop mt-8 space-y-6 text-sm leading-8 text-shop-muted">
            @foreach(preg_split('/\n\s*\n/', trim($store['rulesContent'])) as $section)
                @php
                    $lines = explode("\n", trim($section), 2);
                    $heading = $lines[0] ?? '';
                    $body = $lines[1] ?? '';
                @endphp
                <div>
                    <h2 class="text-base font-black text-shop-text">{{ $heading }}</h2>
                    @if($body)
                        <p class="mt-2">{{ trim($body) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

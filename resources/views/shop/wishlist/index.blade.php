@extends('layouts.shop')

@section('title', 'علاقه‌مندی‌ها')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
<h1 class="mb-6 text-2xl font-bold">علاقه‌مندی‌ها</h1>

@if($products->isEmpty())
    <div class="card p-8 text-center text-shop-muted">
        <p class="mb-4">لیست علاقه‌مندی‌های شما خالی است.</p>
        <a href="{{ route('products.index') }}" class="text-shop-primary hover:underline">مشاهده محصولات</a>
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
@endif
</section>
@endsection

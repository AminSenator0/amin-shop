@extends('layouts.user')

@section('title', 'علاقه‌مندی‌ها')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'علاقه‌مندی‌ها'],
]" />

<x-user.page-header
    title="علاقه‌مندی‌ها"
    subtitle="محصولاتی که برای خرید بعدی ذخیره کرده‌اید."
/>

@if($products->isEmpty())
    <x-user.empty-state
        icon="heart"
        title="لیست علاقه‌مندی‌های شما خالی است"
        description="محصولات مورد علاقه را ذخیره کنید تا بعداً راحت‌تر پیدا و خریداری کنید."
        :action-url="route('products.index')"
        action-label="مشاهده محصولات"
    />
@else
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        @foreach($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
@endif
@endsection

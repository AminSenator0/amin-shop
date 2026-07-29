@extends('layouts.user')

@section('title', 'افزودن آدرس')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'آدرس‌ها', 'url' => route('user.addresses.index')],
    ['label' => 'افزودن آدرس'],
]" />

<x-user.page-header title="افزودن آدرس جدید" subtitle="آدرس تحویل سفارش را با دقت وارد کنید." />

<form method="POST" action="{{ route('user.addresses.store') }}" class="user-info-card space-y-5">
    @csrf
    @include('user.addresses._form')
    <button type="submit" class="btn-primary !text-sm">ذخیره آدرس</button>
</form>
@endsection

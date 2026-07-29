@extends('layouts.user')

@section('title', 'ویرایش آدرس')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'آدرس‌ها', 'url' => route('user.addresses.index')],
    ['label' => 'ویرایش آدرس'],
]" />

<x-user.page-header title="ویرایش آدرس" subtitle="اطلاعات آدرس را به‌روزرسانی کنید." />

<form method="POST" action="{{ route('user.addresses.update', $address) }}" class="user-info-card space-y-5">
    @csrf @method('PUT')
    @include('user.addresses._form')
    <button type="submit" class="btn-primary !text-sm">ذخیره تغییرات</button>
</form>
@endsection

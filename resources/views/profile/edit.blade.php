@extends('layouts.user')

@section('title', 'پروفایل')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'پروفایل'],
]" />

<x-user.page-header
    title="پروفایل من"
    subtitle="اطلاعات حساب، رمز عبور و تنظیمات امنیتی خود را مدیریت کنید."
/>

<div class="space-y-6">
    <div class="user-info-card">
        @include('profile.partials.update-profile-information-form')
    </div>
    <div class="user-info-card">
        @include('profile.partials.update-password-form')
    </div>
    <div class="user-info-card">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection

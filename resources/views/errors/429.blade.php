@extends('errors.layout')

@section('code', '429')
@section('title', 'درخواست‌های زیاد')
@section('message', 'کمی صبر کنید و دوباره تلاش کنید.')

@section('icon')
    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection

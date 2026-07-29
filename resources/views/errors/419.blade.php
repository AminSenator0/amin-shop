@extends('errors.layout')

@section('code', '419')
@section('title', 'نشست منقضی شد')
@section('message', 'صفحه را تازه کنید و دوباره تلاش کنید.')

@section('icon')
    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
    </svg>
@endsection

@section('actions')
    <a href="{{ url()->previous('/') }}" class="btn-primary">تلاش دوباره</a>
    <a href="{{ url('/') }}" class="btn-secondary">صفحه اصلی</a>
@endsection

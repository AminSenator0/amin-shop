@extends('errors.layout')

@section('code', '503')
@section('title', 'سرویس موقتاً در دسترس نیست')
@section('message', 'فروشگاه در حال به‌روزرسانی است. کمی بعد برگردید.')

@section('icon')
    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.099 1.743.188m-1.743-.188a2.548 2.548 0 00-3.586 3.586l6.837 5.63" />
    </svg>
@endsection

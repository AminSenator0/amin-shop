@props(['channel'])

@php
    $code = \App\Services\OtpService::viewDebugCode($channel);
@endphp

@if($code)
    <div {{ $attributes->class('rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950') }}>
        <p class="font-bold">حالت تست — کد تایید شما:</p>
        <p class="mt-2 text-center font-mono text-3xl font-black tracking-[0.4em]" dir="ltr">{{ $code }}</p>
        <p class="mt-2 text-xs text-amber-800/80">ارسال واقعی انجام نشده؛ همین کد را در کادر زیر وارد کنید.</p>
    </div>
@endif

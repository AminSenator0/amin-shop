@props(['status'])

@php
$map = [
    'awaiting_final_price' => ['در انتظار قیمت نهایی', 'bg-amber-100 text-amber-700'],
    'awaiting_payment'     => ['در انتظار پرداخت', 'bg-blue-100 text-blue-700'],
    'partially_paid'       => ['تسویه مرحله‌ای', 'bg-teal-100 text-teal-700'],
    'completed'            => ['تکمیل شده', 'bg-emerald-100 text-emerald-700'],
    'cancelled'            => ['لغو شده', 'bg-rose-100 text-rose-700'],
];
$info = $map[$status] ?? [$status, 'bg-gray-100 text-gray-700'];
@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $info[1] }}">
    {{ $info[0] }}
</span>
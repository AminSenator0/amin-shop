@props(['type' => 'success'])

@php
    $isError = $type === 'error';
    $alertClass = $isError ? 'admin-alert-error' : 'admin-alert-success';
    $iconClass = $isError ? 'text-rose-600' : 'text-emerald-600';
@endphp

<div {{ $attributes->merge(['class' => $alertClass]) }}
     x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     role="alert">
    @if($isError)
        <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    @else
        <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    @endif
    <span>{{ $slot }}</span>
</div>

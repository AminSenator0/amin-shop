@php
    $user = auth()->user();
    $initial = mb_substr($user->name, 0, 1);
@endphp

<div {{ $attributes->merge(['class' => 'user-profile-card']) }}>
    <div class="relative flex items-center gap-4">
        <div class="user-profile-avatar">{{ $initial }}</div>
        <div class="min-w-0 space-y-1">
            <p class="truncate text-lg font-black leading-tight">{{ $user->name }}</p>
            <p class="truncate text-sm leading-5 text-white/75" dir="ltr">{{ $user->email }}</p>
        </div>
    </div>
</div>

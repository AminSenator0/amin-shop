@props(['message', 'showChannelBadge' => false, 'variant' => 'admin'])

@php
    $isUser = $variant === 'user';
    $userBubble = $isUser
        ? 'rounded-2xl rounded-se-sm border border-shop-border/70 bg-shop-background px-4 py-3'
        : 'max-w-[85%] rounded-2xl rounded-se-sm border border-zinc-200 bg-zinc-50 px-4 py-3';
    $adminBubble = $isUser
        ? 'rounded-2xl rounded-se-sm border border-shop-primary/20 bg-shop-primary/5 px-4 py-3'
        : 'max-w-[85%] rounded-2xl rounded-se-sm border border-shop-primary/20 bg-shop-primary/5 px-4 py-3';
    $customerBubble = $isUser
        ? 'rounded-2xl rounded-ss-sm border border-emerald-200 bg-emerald-50 px-4 py-3'
        : 'max-w-[85%] rounded-2xl rounded-ss-sm border border-emerald-100 bg-emerald-50 px-4 py-3';
    $nameClass = $isUser ? 'text-sm font-bold text-shop-text' : 'text-sm font-bold text-zinc-900';
    $timeClass = $isUser ? 'text-xs text-shop-muted' : 'text-xs text-zinc-400';
    $bodyClass = $isUser ? 'text-sm leading-7 text-shop-text whitespace-pre-line' : 'text-sm leading-7 text-zinc-700 whitespace-pre-line';
    $badgeClass = $isUser
        ? 'inline-flex shrink-0 whitespace-nowrap rounded-full bg-shop-primary/10 px-2 py-0.5 text-[10px] font-bold text-shop-primary'
        : 'admin-badge-neutral text-[10px]';
@endphp

<div class="space-y-4">
    <div class="flex justify-start">
        <div class="{{ $isUser ? 'max-w-[90%]' : 'max-w-[85%]' }} {{ $userBubble }}">
            <div class="mb-1.5 flex flex-wrap items-center gap-2">
                <span class="{{ $nameClass }}">{{ $message->name }}</span>
                <span class="{{ $timeClass }}">{{ format_jalali($message->created_at, 'Y/m/d — H:i') }}</span>
                <span class="{{ $badgeClass }}">پیام اولیه</span>
            </div>
            <p class="{{ $bodyClass }}">{{ $message->message }}</p>
        </div>
    </div>

    @foreach($message->replies as $reply)
        <div class="flex {{ $reply->is_from_admin ? 'justify-start' : 'justify-end' }}">
            <div class="{{ $isUser ? 'max-w-[90%]' : 'max-w-[85%]' }} {{ $reply->is_from_admin ? $adminBubble : $customerBubble }}">
                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                    <span class="{{ $nameClass }}">
                        {{ $reply->is_from_admin ? 'پشتیبانی فروشگاه' : ($reply->user?->name ?? $message->name) }}
                    </span>
                    <span class="{{ $timeClass }}">{{ format_jalali($reply->created_at, 'Y/m/d — H:i') }}</span>
                    @if($showChannelBadge && $reply->is_from_admin && $reply->channel)
                        <span class="{{ $badgeClass }}">{{ $reply->channel?->label() ?? 'پنل' }}</span>
                        @if($reply->sms_sent)
                            <span class="admin-badge-info text-[10px]">پیامک ✓</span>
                        @endif
                        @if($reply->email_sent)
                            <span class="admin-badge-info text-[10px]">ایمیل ✓</span>
                        @endif
                    @endif
                </div>
                <p class="{{ $bodyClass }}">{{ $reply->body }}</p>
            </div>
        </div>
    @endforeach
</div>

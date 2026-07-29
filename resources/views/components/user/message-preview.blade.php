@props(['message'])

<a href="{{ route('user.messages.show', $message) }}" class="user-order-card block">
    <div class="mb-2 flex items-start justify-between gap-3">
        <p class="font-black text-shop-text">{{ $message->subject }}</p>
        @if($message->has_unread_reply_for_user)
            <span class="shrink-0 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">جدید</span>
        @endif
    </div>
    <p class="text-xs text-shop-muted">{{ format_jalali($message->last_replied_at ?? $message->created_at, 'Y/m/d — H:i') }}</p>
    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-shop-muted">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</p>
</a>

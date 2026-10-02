@extends('layouts.admin')

@section('header', 'برداشت‌های کیف پول')

@section('content')
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary text-xs">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        <span>داشبورد</span>
    </a>
    @if($pendingCount > 0)
        <span class="admin-badge-warning">{{ $pendingCount }} در انتظار بررسی</span>
    @endif
</div>

{{-- فیلترها --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.wallet.withdrawals.index') }}"
        class="{{ !request('status') ? 'admin-btn-primary' : 'admin-btn-secondary' }} text-xs">همه</a>
    @foreach(['pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'rejected' => 'رد شده'] as $value => $label)
        <a href="{{ route('admin.wallet.withdrawals.index', ['status' => $value]) }}"
            class="{{ request('status') === $value ? 'admin-btn-primary' : 'admin-btn-secondary' }} text-xs">{{ $label }}</a>
    @endforeach
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            <span>لیست برداشت‌ها</span>
        </h2>
        <span class="text-sm text-zinc-500">{{ $withdrawals->total() }} مورد</span>
    </div>

    @if($withdrawals->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>کاربر</th>
                        <th>مبلغ</th>
                        <th>شبا</th>
                        <th>وضعیت</th>
                        <th>یادداشت</th>
                        <th>تاریخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawals as $withdrawal)
                        <tr x-data="{ showReject: false }">
                            <td>
                                <a href="{{ route('admin.users.show', $withdrawal->user) }}" class="font-bold text-zinc-900 hover:text-teal-600">{{ $withdrawal->user->name }}</a>
                                <p class="text-xs text-zinc-400" dir="ltr">{{ $withdrawal->user->email }}</p>
                            </td>
                            <td class="font-bold">{{ format_price($withdrawal->amount) }}</td>
                            <td><span dir="ltr" class="text-xs">{{ $withdrawal->sheba }}</span></td>
                            <td class="whitespace-nowrap">
                                <span class="admin-badge-{{ $withdrawal->status->color() }}">{{ $withdrawal->status->label() }}</span>
                            </td>
                            <td class="text-xs text-zinc-500 max-w-[160px]">
                                {{ $withdrawal->admin_note ?: '—' }}
                            </td>
                            <td class="text-zinc-500 whitespace-nowrap">{{ format_jalali($withdrawal->created_at) }}</td>
                            <td>
                                @if($withdrawal->status->value === 'pending')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('admin.wallet.withdrawals.approve', $withdrawal) }}"
                                            onsubmit="return confirm('تأیید پرداخت {{ format_price($withdrawal->amount) }} به شبای {{ $withdrawal->user->name }}؟\n\nیادتان باشد مبلغ را از کارت بانکی فروشگاه به این شبا واریز کنید.')">
                                            @csrf
                                            <button type="submit" class="admin-btn-primary text-xs w-full">تأیید (واریز کردم)</button>
                                        </form>
                                        <button type="button" @click="showReject = !showReject" class="admin-btn-secondary text-xs w-full">رد و برگشت وجه</button>
                                        <form x-show="showReject" x-cloak method="POST" action="{{ route('admin.wallet.withdrawals.reject', $withdrawal) }}" class="space-y-2">
                                            @csrf
                                            <input type="text" name="admin_note" required placeholder="دلیل رد" class="admin-input w-full text-xs">
                                            <button type="submit" class="admin-btn-secondary text-xs w-full !text-rose-600">ثبت رد و برگشت</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">بررسی شده</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-5 py-4">
            {{ $withdrawals->links() }}
        </div>
    @else
        <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            </div>
            <p class="font-bold text-zinc-700">برداشتی یافت نشد</p>
            <p class="mt-1 text-sm text-zinc-500">هنوز درخواست برداشتی از کیف پول ثبت نشده است.</p>
        </div>
    @endif
</div>
@endsection